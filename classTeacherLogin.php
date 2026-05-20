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
  <title>AMS - Teacher Login</title>
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <style>
      .bg-gradient-login { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
      .login-card { border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border: none; }
      .form-control-modern { border-radius: 10px; padding: 12px 15px; font-size: 14px; border: 2px solid #e9ecef; }
      .form-control-modern:focus { border-color: #11998e; box-shadow: none; }
      .btn-login { background: #11998e; color: white; border-radius: 10px; padding: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; transition: all 0.3s; }
      .btn-login:hover { background: #0e837a; color: white; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(17,153,142,0.3); }
  </style>
</head>

<body class="bg-gradient-login">
  <div class="container-login">
    <div class="row justify-content-center mt-5">
      <div class="col-xl-5 col-lg-6 col-md-8">
        <div class="card login-card my-5">
          <div class="card-body p-0">
            <div class="row">
              <div class="col-lg-12">
                <div class="login-form p-5">
                  <div class="text-center mb-4">
                    <img src="img/logo/attnlg.jpg" style="width:90px;height:90px; border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                    <h4 class="h5 text-gray-900 mt-4 font-weight-bold">Faculty Portal</h4>
                    <p class="text-muted text-sm">Class Teacher Secure Login</p>
                  </div>
                  
                  <form class="user" method="Post" action="">
                    <div class="form-group">
                      <input type="email" class="form-control form-control-modern" required name="username" placeholder="Registered Email Address">
                    </div>
                    <div class="form-group">
                      <input type="password" name="password" required class="form-control form-control-modern" placeholder="Account Password">
                    </div>
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-login btn-block" name="login"><i class="fas fa-sign-in-alt mr-2"></i> Access Dashboard</button>
                    </div>
                  </form>

<?php
  if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password']; 

    // 1. Prepare the query using Oracle bind variables (:username, :password)
    $query = "SELECT * FROM Teacher WHERE tech_email = :username AND password = :password AND active_status = 'Active'";
    $stid = oci_parse($conn, $query);

    // 2. Bind the parameters to prevent SQL injection
    oci_bind_by_name($stid, ":username", $username);
    oci_bind_by_name($stid, ":password", $password);

    // 3. Execute the query
    oci_execute($stid);
    
    // 4. Fetch the results (Oracle returns associative array keys in UPPERCASE)
    if($row = oci_fetch_array($stid, OCI_ASSOC)){
      $_SESSION['userId'] = $row['TEACHER_ID'];
      $_SESSION['firstName'] = $row['FIRST_NAME'];
      $_SESSION['lastName'] = $row['LAST_NAME'];
      $_SESSION['emailAddress'] = $row['TECH_EMAIL'];

      echo "<script type=\"text/javascript\">window.location = (\"ClassTeacher/index.php\")</script>";
    } else {
      echo "<div class='alert alert-danger mt-3' role='alert'><i class='fas fa-times-circle mr-2'></i>Invalid Credentials or Account Suspended!</div>";
    }
  }
?>
                  <hr>
                  <div class="text-center">
                    <a class="font-weight-bold small text-success" href="index.php"><i class="fas fa-arrow-left mr-1"></i> Return to Main Login</a>
                  </div>
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



<!-- The Best Improvement Here
If an Administrator goes into the Manage Teachers page and changes a teacher's status to "Inactive", this new code will immediately block them from logging in. The old code would let them log in forever as long as their password was right. -->