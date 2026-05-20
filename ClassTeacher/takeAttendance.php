<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$teacher_id = $_SESSION['userId']; // Assuming the session stores the teacher_id
$dateTaken = date("Y-m-d");
$timeTaken = date("H:i:s");

// 1. Find the Course Section assigned to this Teacher (Using ROWNUM for Oracle)
$q_section = "SELECT cs.section_id, cs.section_name, c.course_name 
              FROM Course_Section cs 
              INNER JOIN Course c ON c.course_id = cs.course_id 
              WHERE cs.teacher_id = :tid AND ROWNUM <= 1";
$stmt_section = oci_parse($conn, $q_section);
oci_bind_by_name($stmt_section, ":tid", $teacher_id);
oci_execute($stmt_section);

$section_info = oci_fetch_array($stmt_section, OCI_ASSOC);

if(!$section_info) {
    $statusMsg = "<div class='alert alert-danger'>You are not currently assigned to any Course Section. Please contact the Administrator.</div>";
    $section_id = null;
} else {
    $section_id = $section_info['SECTION_ID'];
    $section_display_name = $section_info['COURSE_NAME'] . ' - ' . $section_info['SECTION_NAME'];

    // 2. Find the Active Session Term
    $q_session = "SELECT session_term_id, session_name FROM Session_Term WHERE active_status = 'Active' AND ROWNUM <= 1";
    $stmt_session = oci_parse($conn, $q_session);
    oci_execute($stmt_session);
    $session_info = oci_fetch_array($stmt_session, OCI_ASSOC);
    
    if(!$session_info) {
        $statusMsg = "<div class='alert alert-danger'>No Active Session/Term found. Administrator must set an active term before attendance can be taken.</div>";
        $session_term_id = null;
    } else {
        $session_term_id = $session_info['SESSION_TERM_ID'];

        // 3. Pre-load Attendance Records for Today (Default to Absent if not yet taken)
        $q_chk = "SELECT COUNT(*) AS TOTAL FROM Attendance a 
                  INNER JOIN Enrollment e ON a.enrollment_id = e.enrollment_id 
                  WHERE e.section_id = :secid AND a.attendance_date = TO_DATE(:dt, 'YYYY-MM-DD')";
        $stmt_chk = oci_parse($conn, $q_chk);
        oci_bind_by_name($stmt_chk, ":secid", $section_id);
        oci_bind_by_name($stmt_chk, ":dt", $dateTaken);
        oci_execute($stmt_chk);
        $chk_row = oci_fetch_array($stmt_chk, OCI_ASSOC);
        
        if($chk_row['TOTAL'] == 0) {
            // No attendance taken yet today. Insert default 'Absent' for all enrolled students
            $q_enr = "SELECT enrollment_id FROM Enrollment WHERE section_id = :secid AND enrollment_status = 'Enrolled'";
            $stmt_enr = oci_parse($conn, $q_enr);
            oci_bind_by_name($stmt_enr, ":secid", $section_id);
            oci_execute($stmt_enr);
            
            // Update this specific SQL query in your 'Pre-load' section
$q_ins = "INSERT INTO Attendance (attendance_id, attendance_date, attendance_time, attendance_status, enrollment_id, teacher_id, session_term_id) 
          VALUES (attendance_seq.NEXTVAL, TO_DATE(:dt, 'YYYY-MM-DD'), :tt, 'Absent', :eid, :tid, :stid)";

$stmt_ins = oci_parse($conn, $q_ins);
// Bind your variables...
            oci_bind_by_name($stmt_ins, ":dt", $dateTaken);
            oci_bind_by_name($stmt_ins, ":tt", $timeTaken);
            oci_bind_by_name($stmt_ins, ":tid", $teacher_id);
            oci_bind_by_name($stmt_ins, ":stid", $session_term_id);
            
            while($enr = oci_fetch_array($stmt_enr, OCI_ASSOC)) {
                $e_id = $enr['ENROLLMENT_ID'];
                oci_bind_by_name($stmt_ins, ":eid", $e_id);
                oci_execute($stmt_ins);
            }
        }

        // 4. Handle Form Submission (Saving Attendance)
// 4. Handle Form Submission (Saving Attendance)
if(isset($_POST['save'])){
    // First, reset everyone to 'Absent' for today in this section
    $q_reset = "UPDATE Attendance 
                SET attendance_status = 'Absent' 
                WHERE attendance_date = TO_DATE(:dt, 'YYYY-MM-DD') 
                AND enrollment_id IN (SELECT enrollment_id FROM Enrollment WHERE section_id = :secid)";
    $stmt_reset = oci_parse($conn, $q_reset);
    oci_bind_by_name($stmt_reset, ":dt", $dateTaken);
    oci_bind_by_name($stmt_reset, ":secid", $section_id);
    oci_execute($stmt_reset);

    // Now, update only the checked ones to 'Present'
    if(!empty($_POST['check'])){
        $q_upd = "UPDATE Attendance SET attendance_status = 'Present', attendance_time = :tt 
                  WHERE enrollment_id = :eid AND attendance_date = TO_DATE(:dt, 'YYYY-MM-DD')";
        $stmt_upd = oci_parse($conn, $q_upd);
        oci_bind_by_name($stmt_upd, ":tt", $timeTaken);
        oci_bind_by_name($stmt_upd, ":dt", $dateTaken);
        
        foreach($_POST['check'] as $e_id){
    oci_bind_by_name($stmt_upd, ":eid", $e_id);
    oci_execute($stmt_upd);
        }
        oci_commit($conn); // Commit after loop finishes
            $statusMsg = "<div class='alert alert-success'><i class='fas fa-check-circle mr-2'></i>Attendance successfully updated!</div>";

    }
}
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Take Attendance</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">

  <style>
      :root {
          --primary-color: #4361ee;
          --secondary-color: #3f37c9;
          --success-color: #4cc9f0;
          --danger-color: #f72585;
          --dark-color: #1e1b4b;
          --light-bg: #f8fafc;
          --card-shadow: 0 20px 40px -15px rgba(0,0,0,0.1);
      }
      body { background: var(--light-bg); font-family: 'Inter', sans-serif; }
      .page-header-modern { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 25px 30px; border-radius: 20px; margin-bottom: 30px; box-shadow: var(--card-shadow); color: white; }
      .modern-card { border: none; border-radius: 20px; box-shadow: var(--card-shadow); background: white; margin-bottom: 30px; }
      .card-header-modern { padding: 20px 25px; border-bottom: 2px solid #eef2f6; display: flex; align-items: center; justify-content: space-between; }
      .card-body-modern { padding: 30px; }
      .table-modern { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
      .table-modern thead th { background: #f1f5f9; color: var(--dark-color); font-weight: 700; font-size: 12px; text-transform: uppercase; padding: 15px 20px; border: none; }
      .table-modern tbody tr { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.02); transition: all 0.3s; }
      .table-modern tbody tr:hover { box-shadow: 0 10px 20px rgba(0,0,0,0.05); transform: translateY(-2px); }
      .table-modern tbody td { padding: 15px 20px; vertical-align: middle; border: none; font-size: 14px; }
      .btn-primary { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; padding: 12px 30px; border-radius: 12px; font-weight: 600; text-transform: uppercase; color: white; transition: all 0.3s; }
      .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(67,97,238,0.4); }
      
      /* Custom Checkbox Styling */
      .custom-checkbox { width: 22px; height: 22px; cursor: pointer; accent-color: var(--primary-color); }
  </style>
</head>

<body id="page-top">
  <div id="wrapper">
      <?php include "Includes/sidebar.php";?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
       <?php include "Includes/topbar.php";?>

        <div class="container-fluid" id="container-wrapper">
          
          <div class="page-header-modern d-flex justify-content-between align-items-center">
              <div>
                  <h1 class="h3 m-0 font-weight-bold"><i class="fas fa-clipboard-list mr-3"></i>Take Daily Attendance</h1>
                  <p class="mb-0 mt-2 text-white-50">Assigned Section: <?php echo isset($section_display_name) ? $section_display_name : 'None'; ?></p>
              </div>
              <div class="badge badge-light py-2 px-3 text-dark" style="font-size: 14px;">
                  <i class="far fa-calendar-alt mr-2 text-primary"></i> <?php echo date('l, F j, Y'); ?>
              </div>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="modern-card">
                <div class="card-header-modern">
                  <h6 class="m-0 font-weight-bold" style="color: var(--dark-color);">
                      <i class="fas fa-users mr-2" style="color: var(--primary-color);"></i>Student Roster
                  </h6>
                  <span class="text-danger font-weight-bold text-sm"><i class="fas fa-info-circle mr-1"></i> Check the box if present</span>
                </div>
                
                <div class="card-body-modern">
                  <?php if(isset($statusMsg)) echo $statusMsg; ?>
                  
                  <?php if($section_id != null && $session_term_id != null) { ?>
                  <form method="post">
                    <div class="table-responsive">
                      <table class="table-modern" id="dataTableHover">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Admission No</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Gender</th>
                            <th class="text-center">Present?</th>
                          </tr>
                        </thead>
                        <tbody>
                        <?php
                            // Fetch Enrolled Students and their Current Attendance Status for Today
                            $q_roster = "SELECT s.admission_number, s.student_first_name, s.student_last_name, s.gender, e.enrollment_id, a.attendance_status 
                                         FROM Student s 
                                         INNER JOIN Enrollment e ON s.admission_number = e.admission_id 
                                         LEFT JOIN Attendance a ON e.enrollment_id = a.enrollment_id AND a.attendance_date = TO_DATE(:dt, 'YYYY-MM-DD')
                                         WHERE e.section_id = :secid AND e.enrollment_status = 'Enrolled'
                                         ORDER BY s.student_first_name ASC";
                            
                            $stmt_roster = oci_parse($conn, $q_roster);
                            oci_bind_by_name($stmt_roster, ":dt", $dateTaken);
                            oci_bind_by_name($stmt_roster, ":secid", $section_id);
                            oci_execute($stmt_roster);
                            
                            $sn = 0;
                            
                            $hasData = false;
                            while ($row = oci_fetch_array($stmt_roster, OCI_ASSOC)) {
                                $hasData = true;
                                $sn++;
                                // Determine if checkbox should be checked based on DB status
                                $isChecked = ($row['ATTENDANCE_STATUS'] == 'Present') ? 'checked' : '';
                                
                                echo "
                                <tr>
                                  <td><strong>".$sn."</strong></td>
                                  <td>".$row['ADMISSION_NUMBER']."</td>
                                  <td class='font-weight-bold'>".$row['STUDENT_FIRST_NAME']."</td>
                                  <td class='font-weight-bold'>".$row['STUDENT_LAST_NAME']."</td>
                                  <td>".$row['GENDER']."</td>
                                  <td class='text-center'>
                                      <input name='check[]' type='checkbox' value='".$row['ENROLLMENT_ID']."' class='custom-checkbox' ".$isChecked.">
                                  </td>
                                </tr>";
                            }
                            
                            if(!$hasData) {
                                echo "<tr><td colspan='6' class='text-center py-4 text-muted'><i class='fas fa-folder-open fa-3x mb-3 opacity-50'></i><br>No students are currently enrolled in this section.</td></tr>";
                            }
                        ?>
                        </tbody>
                      </table>
                    </div>
                    <?php if($sn > 0) { ?>
                    <div class="mt-4 pt-3 border-top text-right">
                        <button type="submit" name="save" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Save Attendance</button>
                    </div>
                    <?php } ?>
                  </form>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php include "Includes/footer.php";?>
    </div>
  </div>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
</body>
</html>