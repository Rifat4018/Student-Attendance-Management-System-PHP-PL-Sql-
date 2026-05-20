<?php 
error_reporting(0);
include '../Includes/dbcon.php';

if(isset($_POST['submit'])){
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $phoneNo = $_POST['phoneNo'];
    $password = $_POST['password'];
    $conPassword = $_POST['conPassword'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $state = $_POST['state'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $lga = $_POST['lga'];
    $coopAccountId = $_POST['coopAccountId'];
    $userType = $_POST['userType'];
    $dateCreated = date("Y-m-d");

    // 1. Check if email exists
    $stmt_chk = oci_parse($conn, "SELECT COUNT(*) AS TOTAL FROM users WHERE emailAddress = :email");
    oci_bind_by_name($stmt_chk, ":email", $email);
    oci_execute($stmt_chk);
    $row = oci_fetch_array($stmt_chk, OCI_ASSOC);

    if($password != $conPassword) {
        echo "<script>alert('Password Mismatch!'); window.location='../memberSetup.php';</script>";
    } 
    else if($row['TOTAL'] > 0) {
        echo "<script>alert('Email Address has already been used!'); window.location='../memberSetup.php';</script>";
    }
    else {
        // Shared Insert Query
        $sql_user = "INSERT INTO users (Id, roleId, coopId, firstName, lastName, gender, dob, city, state, lga, emailAddress, address, phoneNo, password, dateCreated) 
                     VALUES (user_seq.NEXTVAL, 2, :coop, :fn, :ln, :gen, TO_DATE(:dob, 'YYYY-MM-DD'), :city, :st, :lga, :email, :addr, :ph, :pass, TO_DATE(:dc, 'YYYY-MM-DD'))";
        
        $stmt_user = oci_parse($conn, $sql_user);
        oci_bind_by_name($stmt_user, ":coop", $coopAccountId);
        oci_bind_by_name($stmt_user, ":fn", $firstName);
        oci_bind_by_name($stmt_user, ":ln", $lastName);
        oci_bind_by_name($stmt_user, ":gen", $gender);
        oci_bind_by_name($stmt_user, ":dob", $dob);
        oci_bind_by_name($stmt_user, ":city", $city);
        oci_bind_by_name($stmt_user, ":st", $state);
        oci_bind_by_name($stmt_user, ":lga", $lga);
        oci_bind_by_name($stmt_user, ":email", $email);
        oci_bind_by_name($stmt_user, ":addr", $address);
        oci_bind_by_name($stmt_user, ":ph", $phoneNo);
        oci_bind_by_name($stmt_user, ":pass", $password);
        oci_bind_by_name($stmt_user, ":dc", $dateCreated);

        if(oci_execute($stmt_user)) {
            // If User is Staff (userType 1)
            if($userType == 1) {
                $companyId = $_POST['companyId'];
                $staffCode = $_POST['staffCode'];
                
                // Get the ID of the newly created user
                $stmt_mid = oci_parse($conn, "SELECT Id FROM users WHERE emailAddress = :email");
                oci_bind_by_name($stmt_mid, ":email", $email);
                oci_execute($stmt_mid);
                $member = oci_fetch_array($stmt_mid, OCI_ASSOC);
                
                $sql_staff = "INSERT INTO companystaff (staffCode, memberId, compId, coopId, position, level, department, jobDescription, dateCreated) 
                              VALUES (:sc, :mid, :cid, :coop, :pos, :lvl, :dep, :desc, TO_DATE(:dc, 'YYYY-MM-DD'))";
                
                $stmt_staff = oci_parse($conn, $sql_staff);
                oci_bind_by_name($stmt_staff, ":sc", $staffCode);
                oci_bind_by_name($stmt_staff, ":mid", $member['ID']);
                oci_bind_by_name($stmt_staff, ":cid", $companyId);
                oci_bind_by_name($stmt_staff, ":coop", $coopAccountId);
                oci_bind_by_name($stmt_staff, ":pos", $_POST['position']);
                oci_bind_by_name($stmt_staff, ":lvl", $_POST['level']);
                oci_bind_by_name($stmt_staff, ":dep", $_POST['department']);
                oci_bind_by_name($stmt_staff, ":desc", $_POST['description']);
                oci_bind_by_name($stmt_staff, ":dc", $dateCreated);

                if(!oci_execute($stmt_staff)) {
                    echo "<script>alert('Error saving staff details!');</script>";
                }
            }
            echo "<script>alert('Created Successfully!'); window.location='../index.php';</script>";
        } else {
            echo "<script>alert('An Error Occurred during registration!');</script>";
        }
    }
}
?>