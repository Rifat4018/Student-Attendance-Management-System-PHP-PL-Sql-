<?php
// Database configuration
$db_host = "localhost"; // Or your IP
$db_port = "1521";
$db_sid  = "xe"; 
$user    = "scott"; 
$pass    = "tiger";

// Build the connection string using Easy Connect syntax (valid for Oracle 10g+)
$db = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$db_host)(PORT=$db_port))(CONNECT_DATA=(SID=$db_sid)))";

// Connect to Oracle
// Adding 'AL32UTF8' is best practice to ensure proper character handling
$conn = oci_connect($user, $pass, $db, 'AL32UTF8');

if (!$conn) {
    $e = oci_error();
    // Using trigger_error is standard for DB connection failures
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

// Optional: If you want to confirm it's working during testing, 
// you can uncomment the line below. Remove it for production.
// echo "Successfully connected to Oracle 10g!";
?>