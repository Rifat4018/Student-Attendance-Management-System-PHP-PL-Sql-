<?php 
include 'Includes/dbcon.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>AMS - Secure Login</title>
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <style>
      .bg-gradient-login { background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
      .login-card { border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border: none; width: 100%; }
      
      /* ADDED height: auto; HERE TO FIX THE CUT-OFF TEXT */
      .form-control-modern { border-radius: 10px; padding: 12px 15px; font-size: 14px; border: 2px solid #e9ecef; height: auto; }
      
      .form-control-modern:focus { border-color: #4361ee; box-shadow: none; }
      .btn-login { background: #4361ee; color: white; border-radius: 10px; padding: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; transition: all 0.3s; }
      .btn-login:hover { background: #3f37c9; color: white; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(67,97,238,0.3); }
  </style>
</head>

<body class="bg-gradient-login">
  <div class="container-login w-100">
    <div class="row justify-content-center">
      <div class="col-xl-6 col-lg-7 col-md-9">
        <div class="card login-card">
          <div class="card-body p-0">
            <div class="row">
              <div class="col-lg-12">
                <div class="login-form p-4 p-md-5">
                  <div class="text-center mb-4">
                    <img src="img/logo/attnlg.jpg" style="width:90px;height:90px; border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                    <h4 class="h4 text-gray-900 mt-4 font-weight-bold">Smart Attendance System</h4>
                    <p class="text-muted">Please sign in to your account</p>
                  </div>
                  
                  <form class="user" method="Post" action="">
                    <div class="form-group">
                        <select required name="userType" class="form-control form-control-modern mb-3">
                          <option value="">-- Select User Role --</option>
                          <option value="Administrator">Administrator</option>
                          <option value="ClassTeacher">Class Teacher</option>
                        </select>
                    </div>
                    <div class="form-group">
                      <input type="email" class="form-control form-control-modern" required name="username" placeholder="Enter Email Address">
                    </div>
                    <div class="form-group">
                      <input type="password" name="password" required class="form-control form-control-modern" placeholder="Enter Password">
                    </div>
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-login btn-block" name="login">Login</button>
                    </div>
                  </form>

<?php
  if(isset($_POST['login'])){

    $userType = $_POST['userType'];
    $username = $_POST['username'];
    $password = $_POST['password']; 

    if($userType == "Administrator"){
      // 1. Prepare Oracle Query with Bind Variables
      $query = "SELECT * FROM Admin WHERE email_address = :username AND password = :password AND active_status = 'Active'";
      $stid = oci_parse($conn, $query);
      
      // 2. Bind the parameters securely
      oci_bind_by_name($stid, ":username", $username);
      oci_bind_by_name($stid, ":password", $password);
      
      // 3. Execute
      oci_execute($stid);
      
      // 4. Fetch the array (Remember: Oracle column names are returned in UPPERCASE)
      if($row = oci_fetch_array($stid, OCI_ASSOC)){
        $_SESSION['userId'] = $row['ADMIN_ID'];
        $_SESSION['firstName'] = $row['FIRST_NAME'];
        $_SESSION['lastName'] = $row['LAST_NAME'];
        $_SESSION['emailAddress'] = $row['EMAIL_ADDRESS'];

        echo "<script type=\"text/javascript\">window.location = (\"Admin/index.php\")</script>";
      } else {
        echo "<div class='alert alert-danger mt-3' role='alert'><i class='fas fa-times-circle mr-2'></i>Invalid Credentials or Inactive Account!</div>";
      }
    }
    else if($userType == "ClassTeacher"){
      // 1. Prepare Oracle Query with Bind Variables
      $query = "SELECT * FROM Teacher WHERE tech_email = :username AND password = :password AND active_status = 'Active'";
      $stid = oci_parse($conn, $query);

      // 2. Bind the parameters securely
      oci_bind_by_name($stid, ":username", $username);
      oci_bind_by_name($stid, ":password", $password);

      // 3. Execute
      oci_execute($stid);

      // 4. Fetch the array
      if($row = oci_fetch_array($stid, OCI_ASSOC)){
        $_SESSION['userId'] = $row['TEACHER_ID'];
        $_SESSION['firstName'] = $row['FIRST_NAME'];
        $_SESSION['lastName'] = $row['LAST_NAME'];
        $_SESSION['emailAddress'] = $row['TECH_EMAIL'];

        echo "<script type=\"text/javascript\">window.location = (\"ClassTeacher/index.php\")</script>";
      } else {
        echo "<div class='alert alert-danger mt-3' role='alert'><i class='fas fa-times-circle mr-2'></i>Invalid Credentials or Inactive Account!</div>";
      }
    }
  }
?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
</body>
</html>


<!-- Here is exactly why the old code is now broken:

Table Names Changed: It is looking for tbladmin and tblclassteacher. Those tables no longer exist. They are now Admin and Teacher.

Column Names Changed: The teacher's email column is now tech_email, and the ID column is teacher_id.

The Dead Sessions: Your old teacher login tried to save classId and classArmId into the browser session. In a proper 3NF database, teachers don't carry those IDs in their profile anymore.

Hashing: The old file still has $password = md5($password);.

Let's fix both of your login files. I have stripped out the hashing, updated the SQL queries to target the new tables, and added a security check to ensure only "Active" users can log in. -->