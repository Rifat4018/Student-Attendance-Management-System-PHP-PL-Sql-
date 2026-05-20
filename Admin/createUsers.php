<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

  if (isset($_GET['status'])){
    $status = $_GET['status'];
    $statusMsg = "";
    if($status == "success"){
          $statusMsg = "<div class='alert alert-success'  style='margin-right:700px;'>Created Successfully!</div>";
    }
    if($status == "fail"){
          $statusMsg = "<div class='alert alert-danger'  style='margin-right:700px;'>An Error Occurred!</div>";
    }
    if($status == "exists"){
          $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Already Exists!</div>";
    }
  }
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="../img/logo/attnlg.jpg" rel="icon">
  <title>COBIS - Dashboard</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">

<script>
function displayCompany(str) {
    if (str == "") {
        document.getElementById("txtHintt").innerHTML = "";
        return;
    } else { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHintt").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","ajaxforCompany2.php?qc="+str,true);
        xmlhttp.send();
    }
}
</script> 

</head>

<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
      <?php include "Includes/sidebar.php";?>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
       <?php include "Includes/topbar.php";?>
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Add Users</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Add Users</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <!-- Form Basic -->
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Add Users</h6>
                    <?php echo isset($statusMsg) ? $statusMsg : ''; ?>
                </div>
                <div class="card-body">
                  <form method="post" action="scripts/saveUsers.php">
                   <div class="form-group row mb-3">
                        <div class="col-xl-6">
                            <label class="form-control-label">FirstName<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" name="firstName" id="exampleInputFirstName" placeholder="First Name">
                        </div>
                        <div class="col-xl-6">
                            <label class="form-control-label">LastName<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" name="lastName" id="exampleInputLastName" placeholder="Last Name">
                        </div>
                    </div>
                     <div class="form-group row mb-3">
                        <div class="col-xl-6">
                            <label class="form-control-label">Gender<span class="text-danger ml-2">*</span></label>
                        <select class="form-control mb-3" name="gender">
                             <option>--Select--</option>
                            <option value="1">Male</option>
                            <option value="2">Female</option>   
                     </select>                                                       
                     </div>
                        <div class="col-xl-6">
                            <label class="form-control-label">Date of Birth<span class="text-danger ml-2">*</span></label>
                      <input type="date" class="form-control" name="dob" id="exampleInputDob" placeholder="Dob">
                        </div>
                    </div>
                     <div class="form-group row mb-3">
                        <div class="col-xl-6">
                            <label class="form-control-label">Email Address<span class="text-danger ml-2">*</span></label>
                      <input type="email" class="form-control" name="email" id="exampleInputEmail" placeholder="Email Address">
                        </div>
                        <div class="col-xl-6">
                            <label class="form-control-label">Phone Number<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" name="phoneNo" id="exampleInputPhone" placeholder="Phone Number">
                        </div>
                    </div>
                   
                     <div class="form-group row mb-3">
                        <div class="col-xl-6">
                            <label class="form-control-label">City<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" name="city" id="exampleInputCity" placeholder="City">
                        </div>
                        <div class="col-xl-6">
                            <label class="form-control-label">State<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" name="state" id="exampleInputState" placeholder="State">
                        </div>
                    </div>
                   <div class="form-group row mb-3">
                        <div class="col-xl-6">
                            <label class="form-control-label">Address<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" name="address" id="exampleInputAddress" placeholder="Address">
                        </div>
                        <div class="col-xl-6">
                            <label class="form-control-label">LGA<span class="text-danger ml-2">*</span></label>
                            <input type="text" class="form-control" name="lga" id="exampleInputLga" placeholder="LGA">                    
                    </div>
                    </div>  
                      <div class="form-group row mb-3">
                        <div class="col-xl-6">
                        <label class="form-control-label">Select Role<span class="text-danger ml-2">*</span></label>
                             <?php
                        $qry= "SELECT * FROM roles WHERE Id != 2 ORDER BY roleName ASC";
                        $stmt = oci_parse($conn, $qry);
                        oci_execute($stmt);
                        
                        echo ' <select required name="roleId" class="form-control mb-3">';
                        echo '<option value="">--Select--</option>';
                        // Using Oracle's uppercase keys
                        while ($rows = oci_fetch_array($stmt, OCI_ASSOC)){
                            echo'<option value="'.$rows['ID'].'" >'.$rows['ROLENAME'].'</option>';
                        }
                        echo '</select>';
                            ?>       
                        </div>
                        <div class="col-xl-6">
                        </div>
                    </div>
                   
                    <button type="submit" name="submit" class="btn btn-primary">Save</button>
                  </form>
                </div>
              </div>

              <!-- Input Group -->
                 <div class="row">
              <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">All Users</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>Role</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Gender</th>
                        <th>Date of Birth</th>
                        <th>City</th>
                        <th>State</th>
                        <th>LGA</th>
                        <th>Email Address</th>
                        <th>Phone No</th>
                        <th>Address</th>
                        <th>Date Registered</th>
                        <th>Delete</th>
                        <th>Edit</th>
                      </tr>
                    </thead>
                    <tfoot>
                      <tr>
                        <th>Role</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Gender</th>
                        <th>Date of Birth</th>
                        <th>City</th>
                        <th>State</th>
                        <th>LGA</th>
                        <th>Email Address</th>
                        <th>Phone No</th>
                        <th>Address</th>
                        <th>Date Registered</th>
                        <th>Delete</th>
                        <th>Edit</th>
                      </tr>
                    </tfoot>
                    <tbody>

                  <?php
                      // Use bind variables for security
                      $query = "SELECT roles.roleName, users.firstName, users.lastName, users.gender, users.dob, users.city, users.state, users.lga, users.emailAddress, users.address, users.phoneNo, users.dateCreated
                                FROM users
                                INNER JOIN roles ON roles.Id = users.roleId
                                WHERE users.coopId = :coopId";

                      $stmt = oci_parse($conn, $query);
                      oci_bind_by_name($stmt, ":coopId", $_SESSION['coopId']);
                      oci_execute($stmt);
                      
                      $hasRecords = false;

                      // Oracle returns column names in UPPERCASE
                      while ($rows = oci_fetch_array($stmt, OCI_ASSOC)) {
                          $hasRecords = true;
                          $gender = ($rows['GENDER'] == "1") ? "Male" : "Female";
                          
                          echo"
                            <tr>
                              <td>".$rows['ROLENAME']."</td>
                              <td>".$rows['FIRSTNAME']."</td>
                              <td>".$rows['LASTNAME']."</td>
                              <td>".$gender."</td>
                              <td>".$rows['DOB']."</td>
                              <td>".$rows['CITY']."</td>
                              <td>".$rows['STATE']."</td>
                              <td>".$rows['LGA']."</td>
                              <td>".$rows['EMAILADDRESS']."</td>
                              <td>".$rows['PHONENO']."</td>
                              <td>".$rows['ADDRESS']."</td>
                              <td>".$rows['DATECREATED']."</td>
                              <td><a href='#'><i class='fas fa-fw fa-trash'></i></a></td>
                              <td><a href='#'><i class='fas fa-fw fa-edit'></i></a></td>
                            </tr>";
                      }
                      
                      if (!$hasRecords) {  
                           echo "<tr><td colspan='14'><div class='alert alert-danger' role='alert'>No Record Found!</div></td></tr>";
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            </div>
          </div>
          <!--Row-->

        </div>
        <!---Container Fluid-->
      </div>
      <!-- Footer -->
       <?php include "Includes/footer.php";?>
      <!-- Footer -->
    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
   <!-- Page level plugins -->
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <!-- Page level custom scripts -->
  <script>
    $(document).ready(function () {
      $('#dataTable').DataTable(); // ID From dataTable 
      $('#dataTableHover').DataTable(); // ID From dataTable with Hover
    });
  </script>
</body>

</html>