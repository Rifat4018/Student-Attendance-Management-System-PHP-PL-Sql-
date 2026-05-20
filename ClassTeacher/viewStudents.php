<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$teacher_id = $_SESSION['userId'];

// 1. Find the Course Section assigned to this Teacher dynamically (Oracle uses ROWNUM)
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
  <title>View Students</title>
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
      .table-modern { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
      .table-modern thead th { background: #f1f5f9; color: var(--dark-color); font-weight: 700; font-size: 12px; text-transform: uppercase; padding: 15px 20px; border: none; white-space: nowrap; }
      .table-modern tbody tr { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.02); transition: all 0.3s; }
      .table-modern tbody tr:hover { box-shadow: 0 10px 20px rgba(0,0,0,0.05); transform: translateY(-2px); }
      .table-modern tbody td { padding: 15px 20px; vertical-align: middle; border: none; font-size: 14px; }
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
                  <h1 class="h3 m-0 font-weight-bold"><i class="fas fa-users mr-3"></i>My Class Roster</h1>
                  <p class="mb-0 mt-2 text-white-50">Assigned Section: <?php echo $assigned_class_name; ?></p>
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
                      <i class="fas fa-list-ol mr-2" style="color: var(--primary-color);"></i>Enrolled Students List
                  </h6>
                </div>
                <div class="card-body-modern">
                  
                  <?php if($section_id == null) { ?>
                     <div class="alert alert-warning" style="border-radius: 12px; border-left: 4px solid #f6c23e;">
                         <i class="fas fa-exclamation-triangle mr-2"></i> You are not assigned to a Course Section. No students to display.
                     </div>
                  <?php } else { ?>
                  
                  <div class="table-responsive">
                    <table class="table-modern" id="dataTableHover">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Admission No</th>
                          <th>First Name</th>
                          <th>Last Name</th>
                          <th>Other Name</th>
                          <th>Gender</th>
                          <th>Email Address</th>
                          <th>Date Enrolled</th>
                        </tr>
                      </thead>
                      <tbody>
                      <?php
                          // 3NF Compliant Query: Find students enrolled in the teacher's specific section
                          $q_std = "SELECT s.admission_number, s.student_first_name, s.student_last_name, s.other_name, 
                                           s.gender, s.student_email, TO_CHAR(e.enrollment_date, 'YYYY-MM-DD') AS ENROLLMENT_DATE_FMT
                                    FROM Student s
                                    INNER JOIN Enrollment e ON s.admission_number = e.admission_id
                                    WHERE e.section_id = :secid AND e.enrollment_status = 'Enrolled'
                                    ORDER BY s.student_first_name ASC";
                                    
                          $stmt_std = oci_parse($conn, $q_std);
                          oci_bind_by_name($stmt_std, ":secid", $section_id);
                          oci_execute($stmt_std);
                          
                          $sn = 0;
                          $hasData = false;
                          
                          while ($rows = oci_fetch_array($stmt_std, OCI_ASSOC)) {
                               $hasData = true;
                               $sn++;
                               $otherName = !empty($rows['OTHER_NAME']) ? $rows['OTHER_NAME'] : '-';
                               
                               echo"
                                  <tr>
                                    <td><strong>".$sn."</strong></td>
                                    <td>".$rows['ADMISSION_NUMBER']."</td>
                                    <td class='font-weight-bold'>".$rows['STUDENT_FIRST_NAME']."</td>
                                    <td class='font-weight-bold'>".$rows['STUDENT_LAST_NAME']."</td>
                                    <td>".$otherName."</td>
                                    <td>".$rows['GENDER']."</td>
                                    <td><a href='mailto:".$rows['STUDENT_EMAIL']."' class='text-primary'>".$rows['STUDENT_EMAIL']."</a></td>
                                    <td><i class='far fa-calendar-alt mr-2 text-muted'></i>".$rows['ENROLLMENT_DATE_FMT']."</td>
                                  </tr>";
                          }
                          
                          if (!$hasData) {
                               echo "<tr><td colspan='8' class='text-center py-5 text-muted'><i class='fas fa-folder-open fa-3x mb-3 opacity-50'></i><br>No students are currently enrolled in your class.</td></tr>";
                          }
                      ?>
                      </tbody>
                    </table>
                  </div>
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