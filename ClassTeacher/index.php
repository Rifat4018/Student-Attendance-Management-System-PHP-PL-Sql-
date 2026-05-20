<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$teacher_id = $_SESSION['userId'];

// 1. Find the Course Section assigned to this Teacher dynamically
$query = "SELECT cs.section_id, cs.section_name, c.course_name 
          FROM Course_Section cs
          INNER JOIN Course c ON c.course_id = cs.course_id
          WHERE cs.teacher_id = :tid AND ROWNUM <= 1";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":tid", $teacher_id);
oci_execute($stmt);

// Check if teacher is actually assigned to a section
if($rrw = oci_fetch_array($stmt, OCI_ASSOC)) {
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
  <title>Teacher Dashboard</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  
  <style>
      body#page-top { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
      
      .page-header-title { font-weight: 800; color: #2c3e50; letter-spacing: 0.5px; }
      
      .dash-card {
          border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
          transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); overflow: hidden;
      }
      .dash-card:hover { transform: translateY(-7px); box-shadow: 0 12px 25px rgba(0,0,0,0.15); }
      
      .dash-card .font-weight-bold, .dash-card .text-muted, .dash-card .h3 { color: #ffffff !important; }
      .dash-card .col-auto i { color: rgba(255, 255, 255, 0.8) !important; transition: transform 0.3s ease; }
      .dash-card:hover .col-auto i { transform: scale(1.15); }

      /* Beautiful Gradients */
      .grad-students { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
      .grad-classes { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
      .grad-arms { background: linear-gradient(135deg, #ff9a44 0%, #fc6076 100%); }
      .grad-attendance { background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); }
      .grad-teachers { background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%); }
      .grad-session { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); }
      .grad-terms { background: linear-gradient(135deg, #8E2DE2 0%, #4A00E0 100%); }
  </style>
</head>

<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php";?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php";?>
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-2">
            <h1 class="h3 mb-0 page-header-title">Teacher Dashboard <span class="badge badge-primary ml-2 px-3 py-2" style="font-size: 14px; vertical-align: middle;"><?php echo $assigned_class_name; ?></span></h1>
            <ol class="breadcrumb bg-white shadow-sm rounded-pill px-3 py-2">
              <li class="breadcrumb-item"><a href="./" class="text-primary">Home</a></li>
              <li class="breadcrumb-item active text-gray-600" aria-current="page">Dashboard</li>
            </ol>
          </div>

          <?php if($section_id == null) { ?>
             <div class="alert alert-warning mb-4" style="border-radius: 12px; border-left: 4px solid #f6c23e;">
                 <i class="fas fa-exclamation-triangle mr-2"></i> <strong>Notice:</strong> You have not been assigned to a Course Section yet. Your student count will show 0. Please contact the administrator.
             </div>
          <?php } ?>

          <div class="row mb-3">
            
            <?php 
            $students = 0;
            if($section_id != null) {
                $q1 = oci_parse($conn, "SELECT COUNT(*) AS TOTAL FROM Enrollment WHERE section_id = :sid AND enrollment_status = 'Enrolled'");       
                oci_bind_by_name($q1, ":sid", $section_id);
                oci_execute($q1);
                $r1 = oci_fetch_array($q1, OCI_ASSOC);
                $students = $r1['TOTAL'];
            }
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card dash-card grad-students h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">My Students</div>
                      <div class="h3 mb-0 mr-3 font-weight-bold"><?php echo $students;?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-users fa-3x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <?php 
             $q2 = oci_parse($conn,"SELECT COUNT(*) AS TOTAL FROM Course");       
             oci_execute($q2);
             $r2 = oci_fetch_array($q2, OCI_ASSOC);
             $class = $r2['TOTAL'];
             ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card dash-card grad-classes h-100">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Total Courses</div>
                      <div class="h3 mb-0 font-weight-bold"><?php echo $class;?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-book-open fa-3x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <?php 
             $q3 = oci_parse($conn,"SELECT COUNT(*) AS TOTAL FROM Course_Section");       
             oci_execute($q3);
             $r3 = oci_fetch_array($q3, OCI_ASSOC);
             $classArms = $r3['TOTAL'];
             ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card dash-card grad-arms h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Total Sections</div>
                      <div class="h3 mb-0 font-weight-bold"><?php echo $classArms;?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-layer-group fa-3x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <?php 
            $q4 = oci_parse($conn,"SELECT COUNT(*) AS TOTAL FROM Attendance WHERE teacher_id = :tid");       
            oci_bind_by_name($q4, ":tid", $teacher_id);
            oci_execute($q4);
            $r4 = oci_fetch_array($q4, OCI_ASSOC);
            $totAttendance = $r4['TOTAL'];
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card dash-card grad-attendance h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Total Attendance Records</div>
                      <div class="h3 mb-0 font-weight-bold"><?php echo $totAttendance;?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-calendar-check fa-3x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <?php 
            $q5 = oci_parse($conn,"SELECT COUNT(*) AS TOTAL FROM Teacher");       
            oci_execute($q5);
            $r5 = oci_fetch_array($q5, OCI_ASSOC);
            $classTeacher = $r5['TOTAL'];
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card dash-card grad-teachers h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Registered Teachers</div>
                      <div class="h3 mb-0 font-weight-bold"><?php echo $classTeacher;?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-chalkboard-teacher fa-3x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          
            <?php 
            $q6 = oci_parse($conn,"SELECT COUNT(*) AS TOTAL FROM Session_Term");       
            oci_execute($q6);
            $r6 = oci_fetch_array($q6, OCI_ASSOC);
            $sessTerm = $r6['TOTAL'];
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card dash-card grad-session h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Session & Terms</div>
                      <div class="h3 mb-0 font-weight-bold"><?php echo $sessTerm;?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-calendar-alt fa-3x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <?php 
            $q7 = oci_parse($conn,"SELECT COUNT(*) AS TOTAL FROM Term");       
            oci_execute($q7);
            $r7 = oci_fetch_array($q7, OCI_ASSOC);
            $termonly = $r7['TOTAL'];
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card dash-card grad-terms h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Terms</div>
                      <div class="h3 mb-0 font-weight-bold"><?php echo $termonly;?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-th fa-3x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          </div>
        </div>
      <?php include 'Includes/footer.php';?>
      </div>
  </div>

  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
</body>

</html>