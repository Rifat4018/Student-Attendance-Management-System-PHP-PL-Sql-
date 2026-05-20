<?php 
  // Prepare the query securely using bind variables
  $query = "SELECT * FROM Teacher WHERE teacher_id = :tid";
  $stmt = oci_parse($conn, $query);
  
  // Bind the session variable
  oci_bind_by_name($stmt, ":tid", $_SESSION['userId']);
  oci_execute($stmt);
  
  // Fetch the result and format the name using Oracle's UPPERCASE array keys
  $fullName = "Unknown User";
  if($rows = oci_fetch_array($stmt, OCI_ASSOC)) {
      $fullName = $rows['FIRST_NAME']." ".$rows['LAST_NAME'];
  }
?>
<nav class="navbar navbar-expand navbar-light bg-gradient-primary topbar mb-4 static-top">
  <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
    <i class="fa fa-bars"></i>
  </button>
  <div class="text-white big" style="margin-left:100px;"></div>
  <ul class="navbar-nav ml-auto">
    <div class="topbar-divider d-none d-sm-block"></div>
    <li class="nav-item dropdown no-arrow">
      <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        <img class="img-profile rounded-circle" src="img/user-icn.png" style="max-width: 60px">
        <span class="ml-2 d-none d-lg-inline text-white small"><b>Welcome <?php echo $fullName;?></b></span>
      </a>
      <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="logout.php">
        <i class="fas fa-power-off fa-fw mr-2 text-danger"></i>
          Logout
        </a>
      </div>
    </li>
  </ul>
</nav>