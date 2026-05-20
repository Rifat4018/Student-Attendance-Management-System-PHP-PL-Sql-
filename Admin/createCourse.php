<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$admin_id = $_SESSION['userId']; 

//------------------------SAVE--------------------------------------------------
if(isset($_POST['save'])){
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code'];
    $credit_hours = $_POST['credit_hours'];
    $active_status = $_POST['active_status'];
   
    // Check if Course Name or Code Already Exists
    $chk_sql = "SELECT * FROM Course WHERE course_name = :cname OR course_code = :ccode";
    $chk_stmt = oci_parse($conn, $chk_sql);
    oci_bind_by_name($chk_stmt, ":cname", $course_name);
    oci_bind_by_name($chk_stmt, ":ccode", $course_code);
    oci_execute($chk_stmt);

    if($ret = oci_fetch_array($chk_stmt, OCI_ASSOC)){ 
        $statusMsg = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle mr-2'></i>This Course Name or Code Already Exists!</div>";
    }
    else{
        // Insert new Course
        // Corrected INSERT for Oracle
$ins_sql = "INSERT INTO Course (course_id, course_name, course_code, credit_hours, active_status, admin_id) 
            VALUES (course_seq.NEXTVAL, :cname, :ccode, :chours, :status, :admin_id)";
        $ins_stmt = oci_parse($conn, $ins_sql);
        oci_bind_by_name($ins_stmt, ":cname", $course_name);
        oci_bind_by_name($ins_stmt, ":ccode", $course_code);
        oci_bind_by_name($ins_stmt, ":chours", $credit_hours);
        oci_bind_by_name($ins_stmt, ":status", $active_status);
        oci_bind_by_name($ins_stmt, ":admin_id", $admin_id);
        
        // In SAVE logic:
if (oci_execute($ins_stmt)) {
    oci_commit($conn); // Make it permanent!
    $statusMsg = "<div class='alert alert-success'>Course Created Successfully!</div>";
} else {
            $statusMsg = "<div class='alert alert-danger'><i class='fas fa-times-circle mr-2'></i>An error Occurred!</div>";
        }
    }
}

//--------------------EDIT------------------------------------------------------------
if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "edit") {
    $Id = $_GET['Id'];
    
    // Fetch current data for the form
    $edit_sql = "SELECT * FROM Course WHERE course_id = :id";
    $edit_stmt = oci_parse($conn, $edit_sql);
    oci_bind_by_name($edit_stmt, ":id", $Id);
    oci_execute($edit_stmt);
    $row = oci_fetch_array($edit_stmt, OCI_ASSOC);

    //------------UPDATE-----------------------------
    if(isset($_POST['update'])){
        $course_name = $_POST['course_name'];
        $course_code = $_POST['course_code'];
        $credit_hours = $_POST['credit_hours'];
        $active_status = $_POST['active_status'];

        $upd_sql = "UPDATE Course SET course_name=:cname, course_code=:ccode, credit_hours=:chours, active_status=:status WHERE course_id=:id";
        $upd_stmt = oci_parse($conn, $upd_sql);
        oci_bind_by_name($upd_stmt, ":cname", $course_name);
        oci_bind_by_name($upd_stmt, ":ccode", $course_code);
        oci_bind_by_name($upd_stmt, ":chours", $credit_hours);
        oci_bind_by_name($upd_stmt, ":status", $active_status);
        oci_bind_by_name($upd_stmt, ":id", $Id);

        // In UPDATE logic:
if (oci_execute($upd_stmt)) {
    oci_commit($conn); // Make it permanent!
    echo "<script>window.location = ('createCourse.php');</script>"; 
} else {
            $statusMsg = "<div class='alert alert-danger'><i class='fas fa-times-circle mr-2'></i>An error Occurred!</div>";
        }
    }
}

//--------------------------------DELETE------------------------------------------------------------------
if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete") {
    $Id = $_GET['Id'];
    
    $del_sql = "DELETE FROM Course WHERE course_id=:id";
    $del_stmt = oci_parse($conn, $del_sql);
    oci_bind_by_name($del_stmt, ":id", $Id);
    
    // In DELETE logic:
if (oci_execute($del_stmt)) {
    oci_commit($conn); // Make it permanent!
    echo "<script>window.location = ('createCourse.php');</script>"; 
} else {
        $statusMsg = "<div class='alert alert-danger'><i class='fas fa-times-circle mr-2'></i>Cannot delete Course. It may have sections tied to it!</div>"; 
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
  <title>Manage Courses</title>
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
              <div><h1 class="h3 m-0 font-weight-bold"><i class="fas fa-book-open mr-3"></i>Manage Courses</h1></div>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="modern-card">
                <div class="card-header-modern">
                  <h6 class="m-0 font-weight-bold" style="color: var(--dark-color);"><i class="fas fa-plus mr-2" style="color: var(--primary-color);"></i><?php echo isset($Id) ? 'Update Course' : 'Create New Course'; ?></h6>
                </div>
                <div class="card-body-modern">
                  <?php if(isset($statusMsg)) echo $statusMsg; ?>
                  <form method="post">
                    <div class="form-group row mb-4">
                        <div class="col-xl-4">
                            <label class="form-label-modern">Course Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control-modern" required name="course_name" value="<?php echo isset($row['COURSE_NAME']) ? $row['COURSE_NAME'] : ''; ?>" placeholder="e.g. Grade 7 Math">
                        </div>
                        <div class="col-xl-3 mt-3 mt-xl-0">
                            <label class="form-label-modern">Course Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control-modern" required name="course_code" value="<?php echo isset($row['COURSE_CODE']) ? $row['COURSE_CODE'] : ''; ?>" placeholder="e.g. MTH101">
                        </div>
                        <div class="col-xl-2 mt-3 mt-xl-0">
                            <label class="form-label-modern">Credit Hours</label>
                            <input type="number" class="form-control-modern" name="credit_hours" value="<?php echo isset($row['CREDIT_HOURS']) ? $row['CREDIT_HOURS'] : ''; ?>" placeholder="e.g. 3">
                        </div>
                        <div class="col-xl-3 mt-3 mt-xl-0">
                            <label class="form-label-modern">Status <span class="text-danger">*</span></label>
                            <select required name="active_status" class="form-control-modern">
                                <option value="Active" <?php if(isset($row['ACTIVE_STATUS']) && $row['ACTIVE_STATUS'] == 'Active') echo 'selected'; ?>>Active</option>
                                <option value="Inactive" <?php if(isset($row['ACTIVE_STATUS']) && $row['ACTIVE_STATUS'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 text-right border-top">
                        <?php if (isset($Id)) { ?>
                        <button type="submit" name="update" class="btn btn-warning shadow-sm"><i class="fas fa-edit mr-2"></i>Update Course</button>
                        <a href="createCourse.php" class="btn btn-secondary ml-2" style="border-radius: 12px; padding: 12px 30px;">Cancel</a>
                        <?php } else { ?>
                        <button type="submit" name="save" class="btn btn-primary shadow-sm"><i class="fas fa-save mr-2"></i>Save Course</button>
                        <?php } ?>
                    </div>
                  </form>
                </div>
              </div>

              <div class="modern-card">
                <div class="card-header-modern">
                  <h6 class="m-0 font-weight-bold" style="color: var(--dark-color);"><i class="fas fa-list mr-2" style="color: var(--primary-color);"></i>All Courses</h6>
                </div>
                <div class="card-body-modern">
                  <div class="table-responsive">
                    <table class="table-modern" id="dataTableHover">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Course Name</th>
                          <th>Code</th>
                          <th>Credits</th>
                          <th>Status</th>
                          <th class="text-center">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                      <?php
                          $query = "SELECT * FROM Course ORDER BY course_name ASC";
                          $stid = oci_parse($conn, $query);
                          oci_execute($stid);

                          $sn=0;
                          while ($rows = oci_fetch_array($stid, OCI_ASSOC)) {
                               $sn++;
                               $badge = ($rows['ACTIVE_STATUS'] == 'Active') ? "<span class='badge badge-success px-3 py-2'>Active</span>" : "<span class='badge badge-danger px-3 py-2'>Inactive</span>";
                               echo"
                                  <tr>
                                    <td><strong>".$sn."</strong></td>
                                    <td class='font-weight-bold'>".$rows['COURSE_NAME']."</td>
                                    <td>".$rows['COURSE_CODE']."</td>
                                    <td>".$rows['CREDIT_HOURS']."</td>
                                    <td>".$badge."</td>
                                    <td class='text-center'>
                                        <a href='?action=edit&Id=".$rows['COURSE_ID']."' style='color: #f8961e; margin-right: 10px;'><i class='fas fa-edit'></i></a>
                                        <a href='?action=delete&Id=".$rows['COURSE_ID']."' style='color: #f72585;' onclick='return confirm(\"Are you sure?\")'><i class='fas fa-trash'></i></a>
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
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script>$(document).ready(function () { $('#dataTableHover').DataTable(); });</script>
</body>
</html>