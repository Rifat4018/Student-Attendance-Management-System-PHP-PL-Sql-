<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$admin_id = $_SESSION['userId'];

//------------------------SAVE ENROLLMENT---------------------------------------
if(isset($_POST['save'])){
    $admission_id = $_POST['admission_id'];
    $section_id = $_POST['section_id'];
    $enrollment_status = $_POST['enrollment_status'];
    $enrollment_date = date("Y-m-d");
    
    // Generate a unique Enrollment ID
    $enrollment_id = "ENR" . date('Ymd') . rand(100,999);
   
    // Check if the student is ALREADY enrolled in this specific section
    $chk_sql = "SELECT * FROM Enrollment WHERE admission_id = :adm_id AND section_id = :sec_id";
    $chk_stmt = oci_parse($conn, $chk_sql);
    oci_bind_by_name($chk_stmt, ":adm_id", $admission_id);
    oci_bind_by_name($chk_stmt, ":sec_id", $section_id);
    oci_execute($chk_stmt);
    
    if(oci_fetch_array($chk_stmt, OCI_ASSOC)){ 
        $statusMsg = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle mr-2'></i>This student is already enrolled in this section!</div>";
    }
    else{
        $ins_sql = "INSERT INTO Enrollment (enrollment_id, enrollment_date, enrollment_status, admission_id, section_id) 
                    VALUES (:eid, TO_DATE(:edate, 'YYYY-MM-DD'), :estatus, :adm_id, :sec_id)";
        $ins_stmt = oci_parse($conn, $ins_sql);
        
        oci_bind_by_name($ins_stmt, ":eid", $enrollment_id);
        oci_bind_by_name($ins_stmt, ":edate", $enrollment_date);
        oci_bind_by_name($ins_stmt, ":estatus", $enrollment_status);
        oci_bind_by_name($ins_stmt, ":adm_id", $admission_id);
        oci_bind_by_name($ins_stmt, ":sec_id", $section_id);

        if (oci_execute($ins_stmt)) {
            $statusMsg = "<div class='alert alert-success'><i class='fas fa-check-circle mr-2'></i>Student Enrolled Successfully!</div>";
        } else {
            $statusMsg = "<div class='alert alert-danger'><i class='fas fa-times-circle mr-2'></i>An error Occurred during enrollment!</div>";
        }
    }
}

//--------------------EDIT ENROLLMENT-------------------------------------------
if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "edit") {
    $Id = $_GET['Id']; // This is the enrollment_id
    $edit_sql = "SELECT * FROM Enrollment WHERE enrollment_id = :id";
    $edit_stmt = oci_parse($conn, $edit_sql);
    oci_bind_by_name($edit_stmt, ":id", $Id);
    oci_execute($edit_stmt);
    $row = oci_fetch_array($edit_stmt, OCI_ASSOC);

    //------------UPDATE-----------------------------
    if(isset($_POST['update'])){
        $section_id = $_POST['section_id'];
        $enrollment_status = $_POST['enrollment_status'];

        $upd_sql = "UPDATE Enrollment SET section_id = :sec_id, enrollment_status = :estatus WHERE enrollment_id = :id";
        $upd_stmt = oci_parse($conn, $upd_sql);
        oci_bind_by_name($upd_stmt, ":sec_id", $section_id);
        oci_bind_by_name($upd_stmt, ":estatus", $enrollment_status);
        oci_bind_by_name($upd_stmt, ":id", $Id);

        if (oci_execute($upd_stmt)) {
            echo "<script type = \"text/javascript\"> window.location = (\"manageEnrollments.php\") </script>"; 
        } else {
            $statusMsg = "<div class='alert alert-danger'><i class='fas fa-times-circle mr-2'></i>An error Occurred!</div>";
        }
    }
}

//--------------------------------DELETE----------------------------------------
if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete") {
    $Id = $_GET['Id'];
    $del_sql = "DELETE FROM Enrollment WHERE enrollment_id = :id";
    $del_stmt = oci_parse($conn, $del_sql);
    oci_bind_by_name($del_stmt, ":id", $Id);
    
    // Suppress warning if a constraint violation happens (like dependent attendance records)
    if (@oci_execute($del_stmt)) {
        echo "<script type = \"text/javascript\"> window.location = (\"manageEnrollments.php\") </script>";  
    } else {
        $statusMsg = "<div class='alert alert-danger'><i class='fas fa-times-circle mr-2'></i>Cannot delete Enrollment. Attendance records may be tied to it!</div>"; 
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
  <title>Manage Enrollments</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  
  <style>
      :root { --primary-color: #4361ee; --secondary-color: #3f37c9; --dark-color: #1e1b4b; --light-bg: #f8fafc; --card-shadow: 0 20px 40px -15px rgba(0,0,0,0.1); }
      body { background: var(--light-bg); font-family: 'Inter', sans-serif; }
      .page-header-modern { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 25px 30px; border-radius: 20px; margin-bottom: 30px; box-shadow: var(--card-shadow); color: white; }
      .modern-card { border: none; border-radius: 20px; box-shadow: var(--card-shadow); background: white; margin-bottom: 30px; overflow: hidden; }
      .card-header-modern { padding: 20px 25px; border-bottom: 2px solid #eef2f6; display: flex; align-items: center; justify-content: space-between; background: white; }
      .card-body-modern { padding: 30px; }
      .form-label-modern { font-weight: 600; font-size: 13px; color: var(--dark-color); text-transform: uppercase; margin-bottom: 8px; display: block; }
      .form-control-modern { border: 2px solid #e9ecef; border-radius: 12px; padding: 12px 18px; font-size: 14px; width: 100%; background: #f8fafc; transition: all 0.3s; }
      .form-control-modern:focus { border-color: var(--primary-color); outline: none; background: white; box-shadow: 0 0 0 4px rgba(67,97,238,0.1); }
      .btn-primary { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; padding: 12px 30px; border-radius: 12px; font-weight: 600; text-transform: uppercase; color: white; transition: all 0.3s; }
      .btn-warning { border-radius: 12px; font-weight: 600; text-transform: uppercase; padding: 12px 30px; }
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
              <div><h1 class="h3 m-0 font-weight-bold"><i class="fas fa-link mr-3"></i>Manage Enrollments</h1></div>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="modern-card">
                <div class="card-header-modern">
                  <h6 class="m-0 font-weight-bold" style="color: var(--dark-color);"><i class="fas fa-plus mr-2" style="color: var(--primary-color);"></i><?php echo isset($Id) ? 'Update Enrollment' : 'Create New Enrollment'; ?></h6>
                </div>
                <div class="card-body-modern">
                  <?php if(isset($statusMsg)) echo $statusMsg; ?>
                  <form method="post">
                    <div class="form-group row mb-4">
                        
                        <div class="col-xl-4">
                            <label class="form-label-modern">Select Student <span class="text-danger">*</span></label>
                            <?php
                            // Fetch all students. If editing, disable this so they can't change the student, just the class.
                            $disabled = isset($Id) ? "disabled" : "";
                            $stdQry = "SELECT admission_number, student_first_name, student_last_name FROM Student ORDER BY student_first_name ASC";
                            $result = oci_parse($conn, $stdQry);
                            oci_execute($result);
                            
                            echo '<select required name="admission_id" class="form-control-modern" '.$disabled.'>';
                            echo '<option value="">-- Select Student --</option>';
                            // Fetch using Oracle's uppercase keys
                            while ($sRow = oci_fetch_array($result, OCI_ASSOC)){
                                $selected = (isset($row['ADMISSION_ID']) && $row['ADMISSION_ID'] == $sRow['ADMISSION_NUMBER']) ? "selected" : "";
                                echo '<option value="'.$sRow['ADMISSION_NUMBER'].'" '.$selected.'>'.$sRow['STUDENT_FIRST_NAME'].' '.$sRow['STUDENT_LAST_NAME'].' ('.$sRow['ADMISSION_NUMBER'].')</option>';
                            }
                            echo '</select>';
                            
                            // Hidden field to pass admission_id if dropdown is disabled during edit
                            if(isset($Id)) echo "<input type='hidden' name='admission_id' value='".$row['ADMISSION_ID']."'>";
                            ?>
                        </div>

                        <div class="col-xl-4 mt-3 mt-xl-0">
                            <label class="form-label-modern">Select Course & Section <span class="text-danger">*</span></label>
                            <?php
                            // Smart Dropdown joining Course and Section natively
                            $secQry = "SELECT cs.section_id, cs.section_name, c.course_name 
                                       FROM Course_Section cs
                                       INNER JOIN Course c ON c.course_id = cs.course_id
                                       ORDER BY c.course_name ASC";
                            $result2 = oci_parse($conn, $secQry);
                            oci_execute($result2);
                            
                            echo '<select required name="section_id" class="form-control-modern">';
                            echo '<option value="">-- Select Destination --</option>';
                            while ($secRow = oci_fetch_array($result2, OCI_ASSOC)){
                                $selected = (isset($row['SECTION_ID']) && $row['SECTION_ID'] == $secRow['SECTION_ID']) ? "selected" : "";
                                echo '<option value="'.$secRow['SECTION_ID'].'" '.$selected.'>'.$secRow['COURSE_NAME'].' - '.$secRow['SECTION_NAME'].'</option>';
                            }
                            echo '</select>';
                            ?>
                        </div>

                        <div class="col-xl-4 mt-3 mt-xl-0">
                            <label class="form-label-modern">Enrollment Status <span class="text-danger">*</span></label>
                            <select required name="enrollment_status" class="form-control-modern">
                                <option value="Enrolled" <?php if(isset($row['ENROLLMENT_STATUS']) && $row['ENROLLMENT_STATUS'] == 'Enrolled') echo 'selected'; ?>>Active / Enrolled</option>
                                <option value="Dropped" <?php if(isset($row['ENROLLMENT_STATUS']) && $row['ENROLLMENT_STATUS'] == 'Dropped') echo 'selected'; ?>>Dropped / Transferred</option>
                            </select>
                        </div>

                    </div>
                    <div class="mt-4 pt-3 text-right border-top">
                        <?php if (isset($Id)) { ?>
                        <button type="submit" name="update" class="btn btn-warning shadow-sm"><i class="fas fa-edit mr-2"></i>Update Enrollment</button>
                        <a href="manageEnrollments.php" class="btn btn-secondary ml-2" style="border-radius: 12px; padding: 12px 30px;">Cancel</a>
                        <?php } else { ?>
                        <button type="submit" name="save" class="btn btn-primary shadow-sm"><i class="fas fa-save mr-2"></i>Enroll Student</button>
                        <?php } ?>
                    </div>
                  </form>
                </div>
              </div>

              <div class="modern-card">
                <div class="card-header-modern">
                  <h6 class="m-0 font-weight-bold" style="color: var(--dark-color);"><i class="fas fa-list mr-2" style="color: var(--primary-color);"></i>Active & Past Enrollments</h6>
                </div>
                <div class="card-body-modern">
                  <div class="table-responsive">
                    <table class="table-modern" id="dataTableHover">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Student Name</th>
                          <th>Admission No</th>
                          <th>Course Name</th>
                          <th>Section</th>
                          <th>Status</th>
                          <th>Date</th>
                          <th class="text-center">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                      <?php
                          // Join to get full context and use TO_CHAR for Oracle date formatting
                          $query = "SELECT e.enrollment_id, e.enrollment_status, TO_CHAR(e.enrollment_date, 'YYYY-MM-DD') AS ENROLLMENT_DATE_FMT,
                                           s.student_first_name, s.student_last_name, s.admission_number,
                                           c.course_name, cs.section_name
                                    FROM Enrollment e
                                    INNER JOIN Student s ON e.admission_id = s.admission_number
                                    INNER JOIN Course_Section cs ON e.section_id = cs.section_id
                                    INNER JOIN Course c ON cs.course_id = c.course_id
                                    ORDER BY e.enrollment_date DESC";
                                    
                          $rs = oci_parse($conn, $query);
                          oci_execute($rs);
                          
                          while ($rows = oci_fetch_array($rs, OCI_ASSOC)) {
                               $badge = ($rows['ENROLLMENT_STATUS'] == 'Enrolled') ? "<span class='badge badge-success px-3 py-2'>Enrolled</span>" : "<span class='badge badge-danger px-3 py-2'>Dropped</span>";
                               
                               echo"
                                  <tr>
                                    <td><strong>".$rows['ENROLLMENT_ID']."</strong></td>
                                    <td class='font-weight-bold'>".$rows['STUDENT_FIRST_NAME']." ".$rows['STUDENT_LAST_NAME']."</td>
                                    <td>".$rows['ADMISSION_NUMBER']."</td>
                                    <td>".$rows['COURSE_NAME']."</td>
                                    <td>".$rows['SECTION_NAME']."</td>
                                    <td>".$badge."</td>
                                    <td><i class='far fa-calendar-alt text-muted mr-1'></i> ".$rows['ENROLLMENT_DATE_FMT']."</td>
                                    <td class='text-center'>
                                        <a href='?action=edit&Id=".$rows['ENROLLMENT_ID']."' style='color: #f8961e; margin-right: 10px;' title='Edit Status/Section'><i class='fas fa-edit'></i></a>
                                        <a href='?action=delete&Id=".$rows['ENROLLMENT_ID']."' style='color: #f72585;' onclick='return confirm(\"Are you sure you want to permanently delete this enrollment record?\")' title='Delete'><i class='fas fa-trash'></i></a>
                                    </td>
                                  </tr>";
                          }
                      ?>
                      </tbody>
                    </table>
                  </div>
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
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script>$(document).ready(function () { $('#dataTableHover').DataTable({ "pageLength": 10 }); });</script>
</body>
</html>