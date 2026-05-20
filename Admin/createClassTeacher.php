<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$admin_id = $_SESSION['userId'];

if(isset($_POST['save'])){
    $teacher_id = $_POST['teacher_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $tech_email = $_POST['tech_email'];
    $teacher_phone = $_POST['teacher_phone'];
    $u_name = $_POST['u_name'];
    $password = $_POST['password']; 
    $active_status = $_POST['active_status'];
    $date_created = date("Y-m-d");

    // Check if Teacher ID or Username already exists
    $check_sql = "SELECT * FROM Teacher WHERE teacher_id = :tid OR u_name = :uname";
    $check_stmt = oci_parse($conn, $check_sql);
    oci_bind_by_name($check_stmt, ":tid", $teacher_id);
    oci_bind_by_name($check_stmt, ":uname", $u_name);

    // DEBUGGING - Add this to see what's happening
echo "Checking DB for ID: " . $teacher_id . " and Username: " . $u_name;
// exit; // Uncomment this to stop and check the output
    oci_execute($check_stmt);

    if(oci_fetch_array($check_stmt, OCI_ASSOC)){ 
        $statusMsg = "<div class='alert alert-danger'>This Teacher ID or Username Already Exists!</div>";
    }
    else {
        try {
            // INSERT Core Data using TO_DATE for Oracle date formatting
            $query1 = "INSERT INTO Teacher (teacher_id, first_name, last_name, tech_email, u_name, password, date_created, active_status, admin_id) 
                       VALUES (:tid, :fname, :lname, :email, :uname, :pass, TO_DATE(:dcreated, 'YYYY-MM-DD'), :status, :admin_id)";
            $stmt1 = oci_parse($conn, $query1);
            
            oci_bind_by_name($stmt1, ":tid", $teacher_id);
            oci_bind_by_name($stmt1, ":fname", $first_name);
            oci_bind_by_name($stmt1, ":lname", $last_name);
            oci_bind_by_name($stmt1, ":email", $tech_email);
            oci_bind_by_name($stmt1, ":uname", $u_name);
            oci_bind_by_name($stmt1, ":pass", $password);
            oci_bind_by_name($stmt1, ":dcreated", $date_created);
            oci_bind_by_name($stmt1, ":status", $active_status);
            oci_bind_by_name($stmt1, ":admin_id", $admin_id);

            // Execute without auto-committing to start the transaction
            if(!oci_execute($stmt1, OCI_NO_AUTO_COMMIT)) {
                $e = oci_error($stmt1);
                throw new Exception("Failed to insert core data: " . $e['message']);
            }

            // INSERT Phone Data
            $query2 = "INSERT INTO Teacher_Phone (teacher_id, teacher_phone) VALUES (:tid, :phone)";
            $stmt2 = oci_parse($conn, $query2);
            oci_bind_by_name($stmt2, ":tid", $teacher_id);
            oci_bind_by_name($stmt2, ":phone", $teacher_phone);

            if(!oci_execute($stmt2, OCI_NO_AUTO_COMMIT)) {
                $e = oci_error($stmt2);
                throw new Exception("Failed to insert phone: " . $e['message']);
            }

            // Commit transaction
            oci_commit($conn);
            $statusMsg = "<div class='alert alert-success'>Teacher Profile Created Successfully!</div>";
        } catch (Exception $e) {
            oci_rollback($conn);
            $statusMsg = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Handle Delete Request
if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete") {
    $Id = $_GET['Id'];
    try {
        $del1 = oci_parse($conn, "DELETE FROM Teacher_Phone WHERE teacher_id = :id");
        oci_bind_by_name($del1, ":id", $Id);
        if(!oci_execute($del1, OCI_NO_AUTO_COMMIT)) throw new Exception("Phone deletion failed.");

        $del2 = oci_parse($conn, "DELETE FROM Teacher WHERE teacher_id = :id");
        oci_bind_by_name($del2, ":id", $Id);
        if(!oci_execute($del2, OCI_NO_AUTO_COMMIT)) throw new Exception("Teacher deletion failed.");

        oci_commit($conn);
        echo "<script> window.location = ('createClassTeacher.php') </script>";
    } catch (Exception $e) {
        oci_rollback($conn);
        $statusMsg = "<div class='alert alert-danger'>Error occurred during deletion.</div>"; 
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
  <title>Manage Teachers</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <style>
      .modern-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); background: white; margin-bottom: 30px; }
      .form-control-modern { border-radius: 10px; padding: 12px; }
  </style>
</head>

<body id="page-top">
  <div id="wrapper">
      <?php include "Includes/sidebar.php";?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
       <?php include "Includes/topbar.php";?>

        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Manage Teachers</h1>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="modern-card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Register New Teacher</h6>
                  <span class="text-danger small">Note: You will assign them to a class on the "Manage Sections" page.</span>
                </div>
                <div class="card-body">
                  <?php if(isset($statusMsg)) echo $statusMsg; ?>
                  <form method="post">
                    <div class="form-group row mb-3">
                        <div class="col-xl-4">
                            <label>Staff ID (e.g. TCH002)<span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-modern" required name="teacher_id">
                        </div>
                        <div class="col-xl-4">
                            <label>First Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-modern" required name="first_name">
                        </div>
                        <div class="col-xl-4">
                            <label>Last Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-modern" required name="last_name">
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <div class="col-xl-4">
                            <label>Email Address<span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-modern" required name="tech_email">
                        </div>
                        <div class="col-xl-4">
                            <label>Phone Number<span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-modern" required name="teacher_phone">
                        </div>
                        <div class="col-xl-4">
                            <label>Status<span class="text-danger">*</span></label>
                            <select required name="active_status" class="form-control form-control-modern">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <div class="col-xl-6">
                            <label>Login Username<span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-modern" required name="u_name">
                        </div>
                        <div class="col-xl-6">
                            <label>Login Password<span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-modern" required name="password">
                        </div>
                    </div>
                    <button type="submit" name="save" class="btn btn-primary mt-3">Register Teacher</button>
                  </form>
                </div>
              </div>

              <div class="modern-card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">All Registered Teachers</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>Staff ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Username</th>
                        <th>Status</th>
                        <th>Delete</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
                        $query = "SELECT t.*, tp.teacher_phone FROM Teacher t LEFT JOIN Teacher_Phone tp ON t.teacher_id = tp.teacher_id ORDER BY t.first_name ASC";
                        $stid = oci_parse($conn, $query);
                        oci_execute($stid);

                        while ($rows = oci_fetch_array($stid, OCI_ASSOC)) {
                            // Map to Oracle's UPPERCASE array keys
                            $badge = ($rows['ACTIVE_STATUS'] == 'Active') ? "<span class='badge badge-success px-2 py-1'>Active</span>" : "<span class='badge badge-danger px-2 py-1'>Inactive</span>";
                            echo"
                              <tr>
                                <td><strong>".$rows['TEACHER_ID']."</strong></td>
                                <td>".$rows['FIRST_NAME']." ".$rows['LAST_NAME']."</td>
                                <td>".$rows['TECH_EMAIL']."</td>
                                <td>".$rows['TEACHER_PHONE']."</td>
                                <td>".$rows['U_NAME']."</td>
                                <td>".$badge."</td>
                                <td><a href='?action=delete&Id=".$rows['TEACHER_ID']."' class='text-danger' onclick='return confirm(\"Delete this teacher entirely?\")'><i class='fas fa-fw fa-trash'></i></a></td>
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
      <?php include "Includes/footer.php";?>
    </div>
  </div>
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script> $(document).ready(function () { $('#dataTableHover').DataTable(); }); </script>
</body>
</html>