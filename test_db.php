<?php
$conn = oci_connect('scott', 'tiger', 'localhost/XE');
if (!$conn) {
    $e = oci_error();
    echo "Connection failed: " . $e['message'];
} else {
    echo "Successfully connected to Oracle 10g!";
}
?>