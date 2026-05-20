<?php
include '../Includes/dbcon.php';

$cid = intval($_GET['cid']);

// 1. Prepare the query using the NEW table and column names
$sql = "SELECT section_id, section_name FROM Course_Section WHERE course_id = :cid";
$stid = oci_parse($conn, $sql);

// 2. Bind the parameter (Oracle best practice for security and performance)
oci_bind_by_name($stid, ":cid", $cid);

// 3. Execute the query
oci_execute($stid);

// Note: You may want to change name="classArmId" to name="section_id" 
// depending on what your receiving POST script expects.
echo '<select required name="section_id" class="form-control mb-3">';
echo '<option value="">--Select Section--</option>';

// 4. Fetch results
// CRITICAL: Oracle returns associative array keys in UPPERCASE by default!
while ($row = oci_fetch_array($stid, OCI_ASSOC)) {
    echo '<option value="'.$row['SECTION_ID'].'">'.$row['SECTION_NAME'].'</option>';
}
echo '</select>';
?>