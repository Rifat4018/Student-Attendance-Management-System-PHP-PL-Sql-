<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$teacher_id = $_SESSION['userId'];
$dateTaken = date("Y-m-d");

// 1. Find the Course Section assigned to this Teacher (Oracle uses ROWNUM instead of LIMIT)
$q1 = "SELECT cs.section_id, cs.section_name, c.course_name 
       FROM Course_Section cs 
       INNER JOIN Course c ON c.course_id = cs.course_id 
       WHERE cs.teacher_id = :tid AND ROWNUM <= 1";
       
$stmt1 = oci_parse($conn, $q1);
oci_bind_by_name($stmt1, ":tid", $teacher_id);
oci_execute($stmt1);

$section_info = oci_fetch_array($stmt1, OCI_ASSOC);

// If no section is assigned, stop the export.
if(!$section_info) {
    echo "Error: You are not assigned to a Course Section. Cannot export records.";
    exit;
}

$section_id = $section_info['SECTION_ID'];
$className = $section_info['COURSE_NAME'];
$sectionName = $section_info['SECTION_NAME'];

// 2. Prepare Excel Headers
$filename = "Attendance_Report_" . $className . "_" . $sectionName . "_" . $dateTaken;

header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=".$filename.".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="9" style="font-size: 18px; font-weight: bold; text-align: center; background-color: #f1f5f9;">
                Daily Attendance Report: <?php echo $className . " - " . $sectionName; ?> (<?php echo date("F j, Y", strtotime($dateTaken)); ?>)
            </th>
        </tr>
        <tr style="background-color: #4361ee; color: white;">
            <th>#</th>
            <th>Admission No</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Other Name</th>
            <th>Gender</th>
            <th>Status</th>
            <th>Time Recorded</th>
            <th>Academic Term</th>
        </tr>
    </thead>
    <tbody>
<?php 
    // 3. The 3NF Query to get today's attendance for enrolled students
    // NOTE: In Oracle, string dates must be converted in JOIN conditions using TO_DATE
    $q2 = "SELECT s.admission_number, s.student_first_name, s.student_last_name, s.other_name, s.gender,
                  a.attendance_status, a.attendance_time,
                  st.session_name, t.term_name
           FROM Student s
           INNER JOIN Enrollment e ON s.admission_number = e.admission_id
           LEFT JOIN Attendance a ON e.enrollment_id = a.enrollment_id AND a.attendance_date = TO_DATE(:dt, 'YYYY-MM-DD')
           LEFT JOIN Session_Term st ON a.session_term_id = st.session_term_id
           LEFT JOIN Term t ON st.term_id = t.term_id
           WHERE e.section_id = :secid AND e.enrollment_status = 'Enrolled'
           ORDER BY s.student_first_name ASC";

    $stmt2 = oci_parse($conn, $q2);
    oci_bind_by_name($stmt2, ":dt", $dateTaken);
    oci_bind_by_name($stmt2, ":secid", $section_id);
    oci_execute($stmt2);

    $cnt = 1;
    $hasData = false;

    while ($row = oci_fetch_array($stmt2, OCI_ASSOC)) { 
        $hasData = true;
        
        // Format Data (Using Oracle's UPPERCASE array keys)
        $status = !empty($row['ATTENDANCE_STATUS']) ? $row['ATTENDANCE_STATUS'] : "Not Taken";
        $colour = ($status == 'Present') ? "#d4edda" : (($status == 'Absent') ? "#f8d7da" : "#fff3cd");
        $timeRecord = !empty($row['ATTENDANCE_TIME']) ? date("h:i A", strtotime($row['ATTENDANCE_TIME'])) : "-";
        $termDisplay = !empty($row['SESSION_NAME']) ? $row['SESSION_NAME'] . " - " . $row['TERM_NAME'] : "-";
        $otherName = !empty($row['OTHER_NAME']) ? $row['OTHER_NAME'] : "-";

        echo '  
        <tr>  
            <td>'.$cnt.'</td> 
            <td>'.$row['ADMISSION_NUMBER'].'</td> 
            <td>'.$row['STUDENT_FIRST_NAME'].'</td> 
            <td>'.$row['STUDENT_LAST_NAME'].'</td> 
            <td>'.$otherName.'</td> 
            <td>'.$row['GENDER'].'</td> 
            <td style="background-color:'.$colour.'; text-align: center; font-weight: bold;">'.$status.'</td>        
            <td>'.$timeRecord.'</td>                       
            <td>'.$termDisplay.'</td> 
        </tr>';
        $cnt++;
    }

    if(!$hasData) {
        echo '<tr><td colspan="9" style="text-align: center; color: red;">No enrolled students found for this section.</td></tr>';
    }
?>
    </tbody>
</table>