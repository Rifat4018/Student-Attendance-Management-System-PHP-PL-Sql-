<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$admin_id = $_SESSION['userId'];

// 1. SAVE LOGIC
if(isset($_POST['save'])){
    $session_name = $_POST['sessionName'];
    $term_id = $_POST['termId'];
    $dateCreated = date("Y-m-d");
   
    // Check if exists
    $chk_sql = "SELECT COUNT(*) as TOTAL FROM Session_Term WHERE session_name = :sname AND term_id = :tid";
    $chk_stmt = oci_parse($conn, $chk_sql);
    oci_bind_by_name($chk_stmt, ":sname", $session_name);
    oci_bind_by_name($chk_stmt, ":tid", $term_id);
    oci_execute($chk_stmt);
    $chk_res = oci_fetch_array($chk_stmt, OCI_ASSOC);

    if($chk_res['TOTAL'] > 0){ 
        $statusMsg = "<div class='alert alert-danger'>This Session and Term Already Exists!</div>";
    } else {
        // IMPORTANT: Use the sequence we created!
        $ins_sql = "INSERT INTO Session_Term (session_term_id, session_name, term_id, active_status, date_created, admin_id) 
                    VALUES (session_term_seq.NEXTVAL, :sname, :tid, 'Inactive', TO_DATE(:dc, 'YYYY-MM-DD'), :aid)";
        $ins_stmt = oci_parse($conn, $ins_sql);
        oci_bind_by_name($ins_stmt, ":sname", $session_name);
        oci_bind_by_name($ins_stmt, ":tid", $term_id);
        oci_bind_by_name($ins_stmt, ":dc", $dateCreated);
        oci_bind_by_name($ins_stmt, ":aid", $admin_id);
        
        // Update this part in your SAVE LOGIC section:
if (oci_execute($ins_stmt)) {
    oci_commit($conn); // <--- Add this! It makes the data permanent.
    $statusMsg = "<div class='alert alert-success'>Created Successfully!</div>";
} else {
    $e = oci_error($ins_stmt); // Helps you debug if it fails
    $statusMsg = "<div class='alert alert-danger'>Error: " . $e['message'] . "</div>";
}
    }
}

// 2. DELETE LOGIC
if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete") {
    $Id = $_GET['Id'];
    $del_stmt = oci_parse($conn, "DELETE FROM Session_Term WHERE session_term_id = :id");
    oci_bind_by_name($del_stmt, ":id", $Id);
    oci_execute($del_stmt);
    echo "<script>window.location = ('createSessionTerm.php');</script>"; 
}

// 3. ACTIVATE LOGIC
if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "activate") {
    $Id = $_GET['Id'];
    oci_execute(oci_parse($conn, "UPDATE Session_Term SET active_status='Inactive'"));
    $act_stmt = oci_parse($conn, "UPDATE Session_Term SET active_status='Active' WHERE session_term_id = :id");
    oci_bind_by_name($act_stmt, ":id", $Id);
    oci_execute($act_stmt);
    echo "<script>window.location = ('createSessionTerm.php');</script>"; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Manage Session & Term</title>
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
            <h1 class="h3 mb-0 text-gray-800">Manage Academic Session & Term</h1>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="modern-card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Create Session and Term</h6>
                </div>
                <div class="card-body">
                  <?php if(isset($statusMsg)) echo $statusMsg; ?>
                  <form method="post">
                    <div class="form-group row mb-3">
                        <div class="col-xl-6">
                            <label class="form-control-label">Academic Year (Session)<span class="text-danger ml-2">*</span></label>
                            <input type="text" class="form-control form-control-modern" name="sessionName" required placeholder="e.g. 2026/2027">
                        </div>
                        <div class="col-xl-6">
                            <label class="form-control-label">Term<span class="text-danger ml-2">*</span></label>
                            <?php
                            $qry= "SELECT * FROM Term ORDER BY term_id ASC";
                            $stmt = oci_parse($conn, $qry);
                            oci_execute($stmt);
                            
                            echo '<select required name="termId" class="form-control form-control-modern">';
                            echo '<option value="">--Select Term--</option>';
                            // Fetch using Oracle's uppercase keys
                            while ($rows = oci_fetch_array($stmt, OCI_ASSOC)){
                                echo'<option value="'.$rows['TERM_ID'].'" >'.$rows['TERM_NAME'].'</option>';
                            }
                            echo '</select>';
                            ?>  
                        </div>
                    </div>
                    <button type="submit" name="save" class="btn btn-primary mt-3">Save Academic Term</button>
                  </form>
                </div>
              </div>

              <div class="modern-card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">All Sessions and Terms</h6>
                  <span class="text-danger small">Click the checkmark to set the active term for the school.</span>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Session</th>
                        <th>Term</th>
                        <th>Status</th>
                        <th>Set Active</th>
                        <th>Delete</th>
                      </tr>
                    </thead>
                    <tbody>
                    // REPLACE YOUR TABLE PHP BLOCK WITH THIS:
<?php
    $query = "SELECT st.session_term_id, st.session_name, st.active_status, t.term_name
              FROM Session_Term st
              INNER JOIN Term t ON t.term_id = st.term_id ORDER BY st.session_term_id DESC";
    $stmt = oci_parse($conn, $query);
    oci_execute($stmt);
    
    $sn = 0;
    while ($rows = oci_fetch_array($stmt, OCI_ASSOC)) {
        // NOTE: Oracle column names are UPPERCASE
        $badge = ($rows['ACTIVE_STATUS'] == 'Active') ? "badge-success" : "badge-secondary";
        $sn++;
        echo "<tr>
                <td>".$sn."</td>
                <td>".$rows['SESSION_NAME']."</td>
                <td>".$rows['TERM_NAME']."</td>
                <td><span class='badge ".$badge." px-3'>".$rows['ACTIVE_STATUS']."</span></td>
                <td><a href='?action=activate&Id=".$rows['SESSION_TERM_ID']."' class='btn btn-sm btn-success'>Activate</a></td>
                <td><a href='?action=delete&Id=".$rows['SESSION_TERM_ID']."' class='text-danger'>Delete</a></td>
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