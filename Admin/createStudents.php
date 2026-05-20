<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

if(isset($_POST['save'])){
    $admission_id = $_POST['admission_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $other_name = $_POST['other_name'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $city_id = $_POST['city_id'];
    
    $date_created = date("Y-m-d");
    $active_status = 'Active';
    $admin_id = $_SESSION['userId'];

    // Check if admission number already exists
    $chk_sql = "SELECT * FROM Student WHERE admission_number = :adm_id";
    $chk_stmt = oci_parse($conn, $chk_sql);
    oci_bind_by_name($chk_stmt, ":adm_id", $admission_id);
    oci_execute($chk_stmt);

    if(oci_fetch_array($chk_stmt, OCI_ASSOC)){ 
        $statusMsg = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle mr-2'></i>This Admission Number Already Exists!</div>";
    }
    else {
        try {
            // Insert Core Student Data
            $query1 = "INSERT INTO Student (admission_number, student_first_name, student_last_name, other_name, gender, dob, student_email, student_date_created, student_active_status, admin_id, city_id) 
                       VALUES (:adm_id, :fname, :lname, :oname, :gender, TO_DATE(:dob, 'YYYY-MM-DD'), :email, TO_DATE(:dcreated, 'YYYY-MM-DD'), :status, :admin_id, :city_id)";
            $stmt1 = oci_parse($conn, $query1);

            oci_bind_by_name($stmt1, ":adm_id", $admission_id);
            oci_bind_by_name($stmt1, ":fname", $first_name);
            oci_bind_by_name($stmt1, ":lname", $last_name);
            oci_bind_by_name($stmt1, ":oname", $other_name);
            oci_bind_by_name($stmt1, ":gender", $gender);
            oci_bind_by_name($stmt1, ":dob", $dob);
            oci_bind_by_name($stmt1, ":email", $email);
            oci_bind_by_name($stmt1, ":dcreated", $date_created);
            oci_bind_by_name($stmt1, ":status", $active_status);
            oci_bind_by_name($stmt1, ":admin_id", $admin_id);
            oci_bind_by_name($stmt1, ":city_id", $city_id);

            // Execute without auto-committing
            if(!oci_execute($stmt1, OCI_NO_AUTO_COMMIT)) {
                $e = oci_error($stmt1);
                throw new Exception("Failed to insert student data: " . $e['message']);
            }

            // Insert Phone Data
            $query2 = "INSERT INTO Student_Phone (admission_id, student_phone) VALUES (:adm_id, :phone)";
            $stmt2 = oci_parse($conn, $query2);
            oci_bind_by_name($stmt2, ":adm_id", $admission_id);
            oci_bind_by_name($stmt2, ":phone", $phone);

            if(!oci_execute($stmt2, OCI_NO_AUTO_COMMIT)) {
                $e = oci_error($stmt2);
                throw new Exception("Failed to insert phone record: " . $e['message']);
            }

            // Commit transaction
            oci_commit($conn);
            $statusMsg = "<div class='alert alert-success'><i class='fas fa-check-circle mr-2'></i>Student Registered Successfully! Now go to Enrollments to assign them to a class.</div>";

        } catch (Exception $e) {
            oci_rollback($conn);
            $statusMsg = "<div class='alert alert-danger'><i class='fas fa-times-circle mr-2'></i>Database Error: " . $e->getMessage() . "</div>";
        }
    }
}

// DELETE STUDENT (Will cascade and delete phone and enrollments automatically based on DB constraints)
if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete") {
    $Id = $_GET['Id'];
    
    $del_sql = "DELETE FROM Student WHERE admission_number = :id";
    $del_stmt = oci_parse($conn, $del_sql);
    oci_bind_by_name($del_stmt, ":id", $Id);

    if (@oci_execute($del_stmt)) {
        echo "<script> window.location = ('createStudents.php') </script>";
    } else {
        $statusMsg = "<div class='alert alert-danger'>An error Occurred! Could not delete student.</div>"; 
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
  <title>Manage Students</title>
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
            <h1 class="h3 mb-0 text-gray-800">Register New Student</h1>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="modern-card">
                <div class="card-body">
                  <?php if(isset($statusMsg)) echo $statusMsg; ?>
                  
                  <form method="post">
                    <h5 class="text-primary border-bottom pb-2 mb-3">1. Personal Information</h5>
                    <div class="form-group row mb-3">
                        <div class="col-xl-4">
                            <label>First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-modern" required name="first_name">
                        </div>
                        <div class="col-xl-4">
                            <label>Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-modern" required name="last_name">
                        </div>
                        <div class="col-xl-4">
                            <label>Other Name</label>
                            <input type="text" class="form-control form-control-modern" name="other_name">
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <div class="col-xl-4">
                            <label>Gender <span class="text-danger">*</span></label>
                            <select required name="gender" class="form-control form-control-modern">
                                <option value="">-- Select --</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-xl-4">
                            <label>Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-modern" required name="dob">
                        </div>
                        <div class="col-xl-4">
                            <label>Admission ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-modern" required name="admission_id" placeholder="e.g. AMS001">
                        </div>
                    </div>

                    <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">2. Contact Information</h5>
                    <div class="form-group row mb-4">
                        <div class="col-xl-4">
                            <label>Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-modern" required name="email">
                        </div>
                        <div class="col-xl-4">
                            <label>Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-modern" required name="phone">
                        </div>
                        <div class="col-xl-4">
                            <label>City <span class="text-danger">*</span></label>
                            <?php
                            $cityQry = "SELECT * FROM Cities ORDER BY city ASC";
                            $cityResult = oci_parse($conn, $cityQry);
                            oci_execute($cityResult);
                            
                            echo '<select required name="city_id" class="form-control form-control-modern">';
                            echo '<option value="">-- Select City --</option>';
                            // Fetch with Oracle uppercase keys
                            while ($cityRow = oci_fetch_array($cityResult, OCI_ASSOC)){
                                echo '<option value="'.$cityRow['CITY_ID'].'">'.$cityRow['CITY'].'</option>';
                            }
                            echo '</select>';
                            ?>
                        </div>
                    </div>
                    <button type="submit" name="save" class="btn btn-primary px-5 mt-3">Register Student</button>
                  </form>
                </div>
              </div>

              <div class="modern-card">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">All Registered Students</h6></div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>Admission No</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Gender</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Delete</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
                        $query = "SELECT s.*, sp.student_phone FROM Student s LEFT JOIN Student_Phone sp ON s.admission_number = sp.admission_id ORDER BY s.student_first_name ASC";
                        $rs = oci_parse($conn, $query);
                        oci_execute($rs);
                        
                        while ($rows = oci_fetch_array($rs, OCI_ASSOC)) {
                            // Using Oracle's uppercase keys
                            echo"
                              <tr>
                                <td><strong>".$rows['ADMISSION_NUMBER']."</strong></td>
                                <td>".$rows['STUDENT_FIRST_NAME']."</td>
                                <td>".$rows['STUDENT_LAST_NAME']."</td>
                                <td>".$rows['GENDER']."</td>
                                <td>".$rows['STUDENT_EMAIL']."</td>
                                <td>".$rows['STUDENT_PHONE']."</td>
                                <td><a href='?action=delete&Id=".$rows['ADMISSION_NUMBER']."' class='text-danger' onclick='return confirm(\"Delete this student? This will also delete their attendance records.\")'><i class='fas fa-trash'></i></a></td>
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
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script> $(document).ready(function () { $('#dataTableHover').DataTable(); }); </script>
</body>
</html>