<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// SAVE LOGIC
if(isset($_POST['save'])){
    $course_id = $_POST['course_id'];
    $section_name = $_POST['section_name'];
    $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : NULL;
    $assignment_status = ($teacher_id != NULL) ? 'Assigned' : 'Unassigned';
   
    // Check if Section Already Exists
    $chk_sql = "SELECT COUNT(*) as TOTAL FROM Course_Section WHERE section_name = :sname AND course_id = :cid";
    $chk_stmt = oci_parse($conn, $chk_sql);
    oci_bind_by_name($chk_stmt, ":sname", $section_name);
    oci_bind_by_name($chk_stmt, ":cid", $course_id);
    oci_execute($chk_stmt);
    $res = oci_fetch_array($chk_stmt, OCI_ASSOC);

    if($res['TOTAL'] > 0){ 
        $statusMsg = "<div class='alert alert-danger'>This Section Already Exists for this Course!</div>";
    }
    else{
        // Use the Sequence for section_id
        $ins_sql = "INSERT INTO Course_Section (section_id, section_name, assignment_status, course_id, teacher_id) 
                    VALUES (course_section_seq.NEXTVAL, :sname, :status, :cid, :tid)";
        $ins_stmt = oci_parse($conn, $ins_sql);
        oci_bind_by_name($ins_stmt, ":sname", $section_name);
        oci_bind_by_name($ins_stmt, ":status", $assignment_status);
        oci_bind_by_name($ins_stmt, ":cid", $course_id);
        oci_bind_by_name($ins_stmt, ":tid", $teacher_id);
        
        if (oci_execute($ins_stmt)) {
            oci_commit($conn); // Make it permanent!
            $statusMsg = "<div class='alert alert-success'>Section Created Successfully!</div>";
        } else {
            $e = oci_error($ins_stmt);
            $statusMsg = "<div class='alert alert-danger'>Error: " . $e['message'] . "</div>";
        }
    }
}
//--------------------EDIT------------------------------------------------------------
if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "edit") {
    $Id = $_GET['Id'];
    
    // Fetch current data for the form
    $edit_sql = "SELECT * FROM Course_Section WHERE section_id = :id";
    $edit_stmt = oci_parse($conn, $edit_sql);
    oci_bind_by_name($edit_stmt, ":id", $Id);
    oci_execute($edit_stmt);
    $row = oci_fetch_array($edit_stmt, OCI_ASSOC);

    //------------UPDATE-----------------------------
    if(isset($_POST['update'])){
        $course_id = $_POST['course_id'];
        $section_name = $_POST['section_name'];
        $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : NULL;
        $assignment_status = ($teacher_id != NULL) ? 'Assigned' : 'Unassigned';

        // Check if teacher is empty before updating to prevent passing empty strings instead of NULL
        if($teacher_id == NULL) {
            $upd_sql = "UPDATE Course_Section SET course_id = :cid, section_name = :sname, assignment_status = :status, teacher_id = NULL WHERE section_id = :id";
            $upd_stmt = oci_parse($conn, $upd_sql);
            oci_bind_by_name($upd_stmt, ":cid", $course_id);
            oci_bind_by_name($upd_stmt, ":sname", $section_name);
            oci_bind_by_name($upd_stmt, ":status", $assignment_status);
            oci_bind_by_name($upd_stmt, ":id", $Id);
        } else {
            $upd_sql = "UPDATE Course_Section SET course_id = :cid, section_name = :sname, assignment_status = :status, teacher_id = :tid WHERE section_id = :id";
            $upd_stmt = oci_parse($conn, $upd_sql);
            oci_bind_by_name($upd_stmt, ":cid", $course_id);
            oci_bind_by_name($upd_stmt, ":sname", $section_name);
            oci_bind_by_name($upd_stmt, ":status", $assignment_status);
            oci_bind_by_name($upd_stmt, ":tid", $teacher_id);
            oci_bind_by_name($upd_stmt, ":id", $Id);
        }

        // ... inside the Update if-block ...
$query = oci_execute($upd_stmt);
if ($query) {
    oci_commit($conn); // Add this
    echo "<script>window.location = ('createCourseSection.php');</script>"; 
} else {
            $statusMsg = "<div class='alert alert-danger'><i class='fas fa-times-circle mr-2'></i>An error Occurred!</div>";
        }
    }
}

//--------------------------------DELETE------------------------------------------------------------------
if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete") {
    $Id = $_GET['Id'];
    
    $del_sql = "DELETE FROM Course_Section WHERE section_id = :id";
    $del_stmt = oci_parse($conn, $del_sql);
    oci_bind_by_name($del_stmt, ":id", $Id);
    
    $query = @oci_execute($del_stmt);
    
    if ($query) {
        echo "<script type = \"text/javascript\"> window.location = (\"createCourseSection.php\") </script>";  
    } else {
        $statusMsg = "<div class='alert alert-danger'><i class='fas fa-times-circle mr-2'></i>Cannot delete Section. It may have students enrolled!</div>"; 
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
  <title>Manage Course Sections</title>
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
              <div><h1 class="h3 m-0 font-weight-bold"><i class="fas fa-layer-group mr-3"></i>Manage Course Sections</h1></div>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="modern-card">
                <div class="card-header-modern">
                  <h6 class="m-0 font-weight-bold" style="color: var(--dark-color);"><i class="fas fa-plus mr-2" style="color: var(--primary-color);"></i><?php echo isset($Id) ? 'Update Section' : 'Create New Section'; ?></h6>
                </div>
                <div class="card-body-modern">
                  <?php if(isset($statusMsg)) echo $statusMsg; ?>
                  <form method="post">
                    <div class="form-group row mb-4">
                        <div class="col-xl-4">
                            <label class="form-label-modern">Select Course <span class="text-danger">*</span></label>
                            <?php
                            $qry= "SELECT * FROM Course ORDER BY course_name ASC";
                            $result = oci_parse($conn, $qry);
                            oci_execute($result);
                            
                            echo '<select required name="course_id" class="form-control-modern">';
                            echo '<option value="">-- Select Course --</option>';
                            while ($cRows = oci_fetch_array($result, OCI_ASSOC)){
                                $selected = (isset($row['COURSE_ID']) && $row['COURSE_ID'] == $cRows['COURSE_ID']) ? "selected" : "";
                                echo '<option value="'.$cRows['COURSE_ID'].'" '.$selected.'>'.$cRows['COURSE_NAME'].'</option>';
                            }
                            echo '</select>';
                            ?>
                        </div>
                        <div class="col-xl-4 mt-3 mt-xl-0">
                            <label class="form-label-modern">Section Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control-modern" required name="section_name" value="<?php echo isset($row['SECTION_NAME']) ? $row['SECTION_NAME'] : ''; ?>" placeholder="e.g. Section A">
                        </div>
                        <div class="col-xl-4 mt-3 mt-xl-0">
                            <label class="form-label-modern">Assign Teacher</label>
                            <?php
                            $qry= "SELECT teacher_id, first_name, last_name FROM Teacher WHERE active_status = 'Active' ORDER BY first_name ASC";
                            $result = oci_parse($conn, $qry);
                            oci_execute($result);
                            
                            echo '<select name="teacher_id" class="form-control-modern">';
                            echo '<option value="">-- Unassigned (Select Later) --</option>';
                            while ($tRows = oci_fetch_array($result, OCI_ASSOC)){
                                $selected = (isset($row['TEACHER_ID']) && $row['TEACHER_ID'] == $tRows['TEACHER_ID']) ? "selected" : "";
                                echo '<option value="'.$tRows['TEACHER_ID'].'" '.$selected.'>'.$tRows['FIRST_NAME'].' '.$tRows['LAST_NAME'].'</option>';
                            }
                            echo '</select>';
                            ?>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 text-right border-top">
                        <?php if (isset($Id)) { ?>
                        <button type="submit" name="update" class="btn btn-warning shadow-sm"><i class="fas fa-edit mr-2"></i>Update Section</button>
                        <a href="createCourseSection.php" class="btn btn-secondary ml-2" style="border-radius: 12px; padding: 12px 30px;">Cancel</a>
                        <?php } else { ?>
                        <button type="submit" name="save" class="btn btn-primary shadow-sm"><i class="fas fa-save mr-2"></i>Save Section</button>
                        <?php } ?>
                    </div>
                  </form>
                </div>
              </div>

              <div class="modern-card">
                <div class="card-header-modern">
                  <h6 class="m-0 font-weight-bold" style="color: var(--dark-color);"><i class="fas fa-list mr-2" style="color: var(--primary-color);"></i>All Course Sections</h6>
                </div>
                <div class="card-body-modern">
                  <div class="table-responsive">
                    <table class="table-modern" id="dataTableHover">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Course Name</th>
                          <th>Section Name</th>
                          <th>Assigned Teacher</th>
                          <th>Status</th>
                          <th class="text-center">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                      <?php
                          // Join to get Course Name and Teacher Name
                          $query = "SELECT cs.section_id, cs.section_name, cs.assignment_status, 
                                           c.course_name, t.first_name, t.last_name 
                                    FROM Course_Section cs
                                    INNER JOIN Course c ON c.course_id = cs.course_id
                                    LEFT JOIN Teacher t ON cs.teacher_id = t.teacher_id
                                    ORDER BY c.course_name, cs.section_name ASC";
                                    
                          $rs = oci_parse($conn, $query);
                          oci_execute($rs);
                          
                          $sn=0;
                          while ($rows = oci_fetch_array($rs, OCI_ASSOC)) {
                               $sn++;
                               $badge = ($rows['ASSIGNMENT_STATUS'] == 'Assigned') ? "<span class='badge badge-success px-3 py-2'>Assigned</span>" : "<span class='badge badge-warning px-3 py-2 text-dark'>Unassigned</span>";
                               $teacherName = ($rows['FIRST_NAME'] != null) ? $rows['FIRST_NAME']." ".$rows['LAST_NAME'] : "<span class='text-muted'><i>None</i></span>";
                               
                               echo"
                                  <tr>
                                    <td><strong>".$sn."</strong></td>
                                    <td class='font-weight-bold'>".$rows['COURSE_NAME']."</td>
                                    <td>".$rows['SECTION_NAME']."</td>
                                    <td>".$teacherName."</td>
                                    <td>".$badge."</td>
                                    <td class='text-center'>
                                        <a href='?action=edit&Id=".$rows['SECTION_ID']."' style='color: #f8961e; margin-right: 10px;'><i class='fas fa-edit'></i></a>
                                        <a href='?action=delete&Id=".$rows['SECTION_ID']."' style='color: #f72585;' onclick='return confirm(\"Are you sure?\")'><i class='fas fa-trash'></i></a>
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