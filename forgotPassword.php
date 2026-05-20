
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
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>RuangAdmin - Login</title>
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">

</head>

<body class="bg-gradient-login">
  <!-- Login Content -->
  <div class="container-login">
    <div class="row justify-content-center">
      <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card shadow-sm my-5">
          <div class="card-body p-0">
            <div class="row">
              <div class="col-lg-12">
                <div class="login-form">
                  <div class="text-center">
                    <img src="img/logo/attnlg.jpg" style="width:100px;height:100px">
                    <br><br>
                    <h1 class="h4 text-gray-900 mb-4">Forgot Password</h1>
                  </div>
                  <form class="user" method="Post" action="">
                    <div class="form-group">
                      <input type="email" class="form-control" required name="email" id="exampleInputEmail" placeholder="Enter Email Address">
                    </div>
                    <div class="form-group">
                      <div class="custom-control custom-checkbox small" style="line-height: 1.5rem;">
                        <input type="checkbox" class="custom-control-input" id="customCheck">
                        <!-- <label class="custom-control-label" for="customCheck">Remember
                          Me</label> -->
                      </div>
                    </div>
                    <div class="form-group">
                        <input type="submit"  class="btn btn-primary btn-block" value="Submit" name="submit" />
                    </div>
                     </form>

                   <?php
  if(isset($_POST['submit'])){
    $email = $_POST['email'];

    // 1. First, check if the email belongs to an Admin
    $admin_query = "SELECT * FROM Admin WHERE email_address = :email";
    $admin_stmt = oci_parse($conn, $admin_query);
    oci_bind_by_name($admin_stmt, ":email", $email);
    oci_execute($admin_stmt);

    if($admin_row = oci_fetch_array($admin_stmt, OCI_ASSOC)){
        // Email exists in Admin table
        echo "<div class='alert alert-success mt-3'>Admin account found. (Add your password reset logic here)</div>";
        
    } else {
        // 2. If not an Admin, check if the email belongs to a Teacher
        $teacher_query = "SELECT * FROM Teacher WHERE tech_email = :email";
        $teacher_stmt = oci_parse($conn, $teacher_query);
        oci_bind_by_name($teacher_stmt, ":email", $email);
        oci_execute($teacher_stmt);

        if($teacher_row = oci_fetch_array($teacher_stmt, OCI_ASSOC)){
            // Email exists in Teacher table
            echo "<div class='alert alert-success mt-3'>Teacher account found. (Add your password reset logic here)</div>";
            
        } else {
            // Email doesn't exist anywhere
            echo "<div class='alert alert-danger mt-3'>Email address not found in our system.</div>";
        }
    }
  }
?>

                    <!-- <hr>
                    <a href="index.html" class="btn btn-google btn-block">
                      <i class="fab fa-google fa-fw"></i> Login with Google
                    </a>
                    <a href="index.html" class="btn btn-facebook btn-block">
                      <i class="fab fa-facebook-f fa-fw"></i> Login with Facebook
                    </a> -->
                  <hr>
                  <div class="text-center">
                    <a class="font-weight-bold small" href="memberSetup.php">Create a Memeber Account!</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <a class="font-weight-bold small" href="organizationSetup.php">Setup Cooperative Account!</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <a class="font-weight-bold small" href="forgotPassword.php">Forgot Password?</a>

                  </div>
                  <div class="text-center">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Login Content -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
</body>

</html>