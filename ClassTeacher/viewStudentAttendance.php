<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$teacher_id = $_SESSION['userId'];

// 1. Find the Course Section assigned to this Teacher dynamically
$q_sec = "SELECT cs.section_id, cs.section_name, c.course_name 
          FROM Course_Section cs
          INNER JOIN Course c ON c.course_id = cs.course_id
          WHERE cs.teacher_id = :tid AND ROWNUM <= 1";

$stmt_sec = oci_parse($conn, $q_sec);
oci_bind_by_name($stmt_sec, ":tid", $teacher_id);
oci_execute($stmt_sec);

if($rrw = oci_fetch_array($stmt_sec, OCI_ASSOC)) {
    $section_id = $rrw['SECTION_ID'];
    $assigned_class_name = $rrw['COURSE_NAME'] . ' - ' . $rrw['SECTION_NAME'];
} else {
    $section_id = null;
    $assigned_class_name = "Unassigned";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>View Student Attendance</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">

  <style>
      :root {
          --primary-color: #4361ee;
          --secondary-color: #3f37c9;
          --dark-color: #1e1b4b;
          --light-bg: #f8fafc;
          --card-shadow: 0 20px 40px -15px rgba(0,0,0,0.1);
      }
      body { background: var(--light-bg); font-family: 'Inter', sans-serif; }
      .page-header-modern { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 25px 30px; border-radius: 20px; margin-bottom: 30px; box-shadow: var(--card-shadow); color: white; }
      .modern-card { border: none; border-radius: 20px; box-shadow: var(--card-shadow); background: white; margin-bottom: 30px; overflow: hidden; }
      .card-header-modern { padding: 20px 25px; border-bottom: 2px solid #eef2f6; display: flex; align-items: center; justify-content: space-between; background: white; }
      .card-body-modern { padding: 30px; }
      .form-label-modern { font-weight: 600; font-size: 13px; color: var(--dark-color); text-transform: uppercase; margin-bottom: 8px; display: block; }
      .form-control-modern { border: 2px solid #e9ecef; border-radius: 12px; padding: 12px 18px; font-size: 14px; width: 100%; background: #f8fafc; transition: all 0.3s; }
      .form-control-modern:focus { border-color: var(--primary-color); outline: none; background: white; box-shadow: 0 0 0 4px rgba(67,97,238,0.1); }
      .btn-primary { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; padding: 12px 30px; border-radius: 12px; font-weight: 600; text-transform: uppercase; color: white; transition: all 0.3s; }
      .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(67,97,238,0.4); }
      .table-modern { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
      .table-modern thead th { background: #f1f5f9; color: var(--dark-color); font-weight: 700; font-size: 12px; text-transform: uppercase; padding: 15px 20px; border: none; white-space: nowrap; }
      .table-modern tbody tr { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.02); transition: all 0.3s; }
      .table-modern tbody tr:hover { box-shadow: 0 10px 20px rgba(0,0,0,0.05); transform: translateY(-2px); }
      .table-modern tbody td { padding: 15px 20px; vertical-align: middle; border: none; font-size: 14px; }
      .status-present { background-color: rgba(40, 167, 69, 0.1); color: #28a745; font-weight: 700; padding: 5px 12px; border-radius: 6px; }
      .status-absent { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; font-weight: 700; padding: 5px 12px; border-radius: 6px; }
  </style>

  <script>
    function typeDropDown(str) {
        if (str == "") {
            document.getElementById("txtHint").innerHTML = "";
            return;
        } else { 
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("txtHint").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET","ajaxCallTypes.php?tid="+str,true);
            xmlhttp.send();
        }
    }
  </script>
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
                  <h1 class="h3 m-0 font-weight-bold"><i class="fas fa-user-clock mr-3"></i>Student Attendance History</h1>
                  <p class="mb-0 mt-2 text-white-50">Assigned Section: <?php echo $assigned_class_name; ?></p>
              </div>
          </div>

          <div class="row">
            <div class="col-lg-12">
              
              <div class="modern-card">
                <div class="card-header-modern">
                  <h6 class="m-0 font-weight-bold" style="color: var(--dark-color);">
                      <i class="fas fa-filter mr-2" style="color: var(--primary-color);"></i>Filter Parameters
                  </h6>
                </div>
                <div class="card-body-modern">
                  <?php if($section_id == null) { ?>
                     <div class="alert alert-warning" style="border-radius: 12px; border-left: 4px solid #f6c23e;">
                         <i class="fas fa-exclamation-triangle mr-2"></i> You are not assigned to a Course Section. You cannot view attendance.
                     </div>
                  <?php } else { ?>
                  
                  <form method="post">
                    <div class="form-group row mb-0">
                        <div class="col-xl-6">
                            <label class="form-label-modern">Select Student <span class="text-danger">*</span></label>
                            <?php
                            // Fetch only students enrolled in this teacher's section
                            $qry = "SELECT s.admission_number, s.student_first_name, s.student_last_name 
                                    FROM Student s
                                    INNER JOIN Enrollment e ON s.admission_number = e.admission_id
                                    WHERE e.section_id = :secid AND e.enrollment_status = 'Enrolled'
                                    ORDER BY s.student_first_name ASC";
                            $stmt_std = oci_parse($conn, $qry);
                            oci_bind_by_name($stmt_std, ":secid", $section_id);
                            oci_execute($stmt_std);
                            
                            echo '<select required name="admissionNumber" class="form-control-modern">';
                            echo '<option value="">-- Select Enrolled Student --</option>';
                            $hasStudents = false;
                            
                            while ($rows = oci_fetch_array($stmt_std, OCI_ASSOC)){
                                $hasStudents = true;
                                // Keep selection selected after submit
                                $selected = (isset($_POST['admissionNumber']) && $_POST['admissionNumber'] == $rows['ADMISSION_NUMBER']) ? "selected" : "";
                                echo '<option value="'.$rows['ADMISSION_NUMBER'].'" '.$selected.'>'.$rows['STUDENT_FIRST_NAME'].' '.$rows['STUDENT_LAST_NAME'].' ('.$rows['ADMISSION_NUMBER'].')</option>';
                            }
                            echo '</select>';
                            
                            if (!$hasStudents) {
                                echo '<input type="text" class="form-control-modern mt-2" disabled placeholder="No students enrolled in this section">';
                            }
                            ?>  
                        </div>
                        
                        <div class="col-xl-6 mt-3 mt-xl-0">
                            <label class="form-label-modern">Query Type <span class="text-danger">*</span></label>
                            <select required name="type" onchange="typeDropDown(this.value)" class="form-control-modern">
                                <option value="">-- Select Query Type --</option>
                                <option value="1" <?php echo (isset($_POST['type']) && $_POST['type'] == '1') ? "selected" : ""; ?>>All Records</option>
                                <option value="2" <?php echo (isset($_POST['type']) && $_POST['type'] == '2') ? "selected" : ""; ?>>By Single Date</option>
                                <option value="3" <?php echo (isset($_POST['type']) && $_POST['type'] == '3') ? "selected" : ""; ?>>By Date Range</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id='txtHint'></div>
                    
                    <div class="mt-4 pt-3 text-right border-top">
                        <button type="submit" name="view" class="btn btn-primary shadow-sm"><i class="fas fa-search mr-2"></i>Fetch History</button>
                    </div>
                  </form>
                  <?php } ?>
                </div>
              </div>

              <?php if(isset($_POST['view']) && $section_id != null) { ?>
              <div class="modern-card">
                <div class="card-header-modern">
                  <h6 class="m-0 font-weight-bold" style="color: var(--dark-color);">
                      <i class="fas fa-history mr-2" style="color: var(--primary-color);"></i>Attendance Results
                  </h6>
                </div>
                <div class="card-body-modern">
                  <div class="table-responsive">
                    <table class="table-modern" id="dataTableHover">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Date</th>
                          <th>Student Name</th>
                          <th>Admission No</th>
                          <th>Academic Term</th>
                          <th>Time Recorded</th>
                          <th class="text-center">Status</th>
                        </tr>
                      </thead>
                      <tbody>
                      <?php
                          $admissionNumber = $_POST['admissionNumber'];
                          $type = $_POST['type'];
                          
                          // Base Query for 3NF Structure
                          $baseQuery = "SELECT s.admission_number, s.student_first_name, s.student_last_name, 
                                               a.attendance_date, a.attendance_time, a.attendance_status, 
                                               st.session_name, t.term_name
                                        FROM Attendance a
                                        INNER JOIN Enrollment e ON a.enrollment_id = e.enrollment_id
                                        INNER JOIN Student s ON e.admission_id = s.admission_number
                                        INNER JOIN Session_Term st ON a.session_term_id = st.session_term_id
                                        INNER JOIN Term t ON st.term_id = t.term_id
                                        WHERE e.section_id = :secid AND s.admission_number = :adm_no";

                          // Dynamically build query and prepare statement based on type
                          if($type == "1"){ 
                              $query = $baseQuery . " ORDER BY a.attendance_date DESC";
                              $stmt_att = oci_parse($conn, $query);
                              oci_bind_by_name($stmt_att, ":secid", $section_id);
                              oci_bind_by_name($stmt_att, ":adm_no", $admissionNumber);
                          }
                          else if($type == "2"){ 
                              $singleDate = $_POST['singleDate'];
                              $query = $baseQuery . " AND a.attendance_date = TO_DATE(:sdate, 'YYYY-MM-DD') ORDER BY a.attendance_date DESC";
                              $stmt_att = oci_parse($conn, $query);
                              oci_bind_by_name($stmt_att, ":secid", $section_id);
                              oci_bind_by_name($stmt_att, ":adm_no", $admissionNumber);
                              oci_bind_by_name($stmt_att, ":sdate", $singleDate);
                          }
                          else if($type == "3"){ 
                              $fromDate = $_POST['fromDate'];
                              $toDate = $_POST['toDate'];
                              $query = $baseQuery . " AND a.attendance_date BETWEEN TO_DATE(:fdate, 'YYYY-MM-DD') AND TO_DATE(:tdate, 'YYYY-MM-DD') ORDER BY a.attendance_date DESC";
                              $stmt_att = oci_parse($conn, $query);
                              oci_bind_by_name($stmt_att, ":secid", $section_id);
                              oci_bind_by_name($stmt_att, ":adm_no", $admissionNumber);
                              oci_bind_by_name($stmt_att, ":fdate", $fromDate);
                              oci_bind_by_name($stmt_att, ":tdate", $toDate);
                          }

                          oci_execute($stmt_att);
                          $sn=0;
                          $hasData = false;
                          
                          while ($rows = oci_fetch_array($stmt_att, OCI_ASSOC)) {
                               $hasData = true;
                               $sn++;
                               
                               // Style the status badge
                               $statusBadge = ($rows['ATTENDANCE_STATUS'] == 'Present') 
                                   ? "<span class='status-present'><i class='fas fa-check mr-1'></i>Present</span>" 
                                   : "<span class='status-absent'><i class='fas fa-times mr-1'></i>Absent</span>";
                               
                               $timeRecorded = !empty($rows['ATTENDANCE_TIME']) ? date("h:i A", strtotime($rows['ATTENDANCE_TIME'])) : "-";
                               $formattedDate = date("F j, Y", strtotime($rows['ATTENDANCE_DATE']));
                               
                               echo"
                                  <tr>
                                    <td><strong>".$sn."</strong></td>
                                    <td><i class='far fa-calendar-alt mr-2 text-muted'></i>".$formattedDate."</td>
                                    <td class='font-weight-bold'>".$rows['STUDENT_FIRST_NAME']." ".$rows['STUDENT_LAST_NAME']."</td>
                                    <td>".$rows['ADMISSION_NUMBER']."</td>
                                    <td>".$rows['SESSION_NAME']." - ".$rows['TERM_NAME']."</td>
                                    <td>".$timeRecorded."</td>
                                    <td class='text-center'>".$statusBadge."</td>
                                  </tr>";
                          }
                          
                          if(!$hasData) {
                               echo "<tr><td colspan='7' class='text-center py-5 text-muted'>
                                    <i class='fas fa-search-minus fa-3x mb-3 opacity-50'></i><br>
                                    No attendance records found for this student under the specified parameters.
                                    </td></tr>";
                          }
                      ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <?php } ?>

            </div>
          </div>
        </div>
      </div>
      <?php include "Includes/footer.php";?>
    </div>
  </div>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#dataTableHover').DataTable({ "pageLength": 10 });
    });
  </script>
</body>
</html>