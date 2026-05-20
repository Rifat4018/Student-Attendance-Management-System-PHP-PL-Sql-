-- phpMyAdmin SQL Dump
-- Generation Time: Mar 23, 2026
-- Server version: 10.4.17-MariaDB
-- PHP Version: 8.0.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `attendancemsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `Admin`
--
CREATE TABLE `Admin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email_address` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `account_created_date` date NOT NULL,
  `active_status` varchar(20) NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `Admin`
INSERT INTO `Admin` (`admin_id`, `first_name`, `last_name`, `email_address`, `password`, `account_created_date`, `active_status`) VALUES
(1, 'Super', 'Admin', 'admin@mail.com', 'admin', '2026-01-01', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `Cities`
--
CREATE TABLE `Cities` (
  `city_id` int(11) NOT NULL AUTO_INCREMENT,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'Unknown',
  PRIMARY KEY (`city_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `Cities`
INSERT INTO `Cities` (`city_id`, `city`, `country`) VALUES
(1, 'Dhaka', 'Bangladesh'),
(2, 'Chittagong', 'Bangladesh');

-- --------------------------------------------------------

--
-- Table structure for table `Term`
--
CREATE TABLE `Term` (
  `term_id` int(11) NOT NULL AUTO_INCREMENT,
  `term_name` varchar(50) NOT NULL,
  `admin_id` int(11) NOT NULL,
  PRIMARY KEY (`term_id`),
  FOREIGN KEY (`admin_id`) REFERENCES `Admin`(`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `Term`
INSERT INTO `Term` (`term_id`, `term_name`, `admin_id`) VALUES
(1, 'First Term', 1),
(2, 'Second Term', 1),
(3, 'Third Term', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Session_Term`
--
CREATE TABLE `Session_Term` (
  `session_term_id` int(11) NOT NULL AUTO_INCREMENT,
  `session_name` varchar(50) NOT NULL,
  `active_status` varchar(20) NOT NULL DEFAULT 'Inactive',
  `date_created` date NOT NULL,
  `term_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  PRIMARY KEY (`session_term_id`),
  FOREIGN KEY (`term_id`) REFERENCES `Term`(`term_id`),
  FOREIGN KEY (`admin_id`) REFERENCES `Admin`(`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `Session_Term`
INSERT INTO `Session_Term` (`session_term_id`, `session_name`, `active_status`, `date_created`, `term_id`, `admin_id`) VALUES
(1, '2025/2026', 'Active', '2026-01-01', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `Teacher`
--
CREATE TABLE `Teacher` (
  `teacher_id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `tech_email` varchar(100) NOT NULL,
  `u_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_created` date NOT NULL,
  `active_status` varchar(20) NOT NULL DEFAULT 'Active',
  `admin_id` int(11) NOT NULL,
  PRIMARY KEY (`teacher_id`),
  FOREIGN KEY (`admin_id`) REFERENCES `Admin`(`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `Teacher`
INSERT INTO `Teacher` (`teacher_id`, `first_name`, `last_name`, `tech_email`, `u_name`, `password`, `date_created`, `active_status`, `admin_id`) VALUES
('TCH001', 'John', 'Keroche', 'teacher@mail.com', 'jkeroche', 'pass123', '2026-03-01', 'Active', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Teacher_Phone`
--
CREATE TABLE `Teacher_Phone` (
  `teacher_id` varchar(50) NOT NULL,
  `teacher_phone` varchar(20) NOT NULL,
  PRIMARY KEY (`teacher_id`, `teacher_phone`),
  FOREIGN KEY (`teacher_id`) REFERENCES `Teacher`(`teacher_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Teacher_Phone` (`teacher_id`, `teacher_phone`) VALUES ('TCH001', '0100000030');

-- --------------------------------------------------------

--
-- Table structure for table `Course`
--
CREATE TABLE `Course` (
  `course_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_name` varchar(150) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `credit_hours` int(11) DEFAULT 0,
  `active_status` varchar(20) NOT NULL DEFAULT 'Active',
  `admin_id` int(11) NOT NULL,
  PRIMARY KEY (`course_id`),
  FOREIGN KEY (`admin_id`) REFERENCES `Admin`(`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `Course`
INSERT INTO `Course` (`course_id`, `course_name`, `course_code`, `credit_hours`, `active_status`, `admin_id`) VALUES
(1, 'Grade Nine', 'G9', 0, 'Active', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Course_Section`
--
CREATE TABLE `Course_Section` (
  `section_id` int(11) NOT NULL AUTO_INCREMENT,
  `section_name` varchar(100) NOT NULL,
  `assignment_status` varchar(30) NOT NULL DEFAULT 'Unassigned',
  `course_id` int(11) NOT NULL,
  `teacher_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`section_id`),
  FOREIGN KEY (`course_id`) REFERENCES `Course`(`course_id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `Teacher`(`teacher_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `Course_Section`
INSERT INTO `Course_Section` (`section_id`, `section_name`, `assignment_status`, `course_id`, `teacher_id`) VALUES
(1, 'N1', 'Assigned', 1, 'TCH001');

-- --------------------------------------------------------

--
-- Table structure for table `Student`
--
CREATE TABLE `Student` (
  `admission_number` varchar(50) NOT NULL,
  `student_first_name` varchar(100) NOT NULL,
  `student_last_name` varchar(100) NOT NULL,
  `other_name` varchar(100) DEFAULT NULL,
  `gender` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `student_email` varchar(100) NOT NULL,
  `student_date_created` date NOT NULL,
  `student_active_status` varchar(20) NOT NULL DEFAULT 'Active',
  `admin_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  PRIMARY KEY (`admission_number`),
  FOREIGN KEY (`admin_id`) REFERENCES `Admin`(`admin_id`),
  FOREIGN KEY (`city_id`) REFERENCES `Cities`(`city_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `Student`
INSERT INTO `Student` (`admission_number`, `student_first_name`, `student_last_name`, `other_name`, `gender`, `dob`, `student_email`, `student_date_created`, `student_active_status`, `admin_id`, `city_id`) VALUES
('AMS110', 'Jon', 'Mbeeka', 'none', 'Male', '2010-05-15', 'jon@student.com', '2026-03-01', 'Active', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `Student_Phone`
--
CREATE TABLE `Student_Phone` (
  `admission_id` varchar(50) NOT NULL,
  `student_phone` varchar(20) NOT NULL,
  PRIMARY KEY (`admission_id`, `student_phone`),
  FOREIGN KEY (`admission_id`) REFERENCES `Student`(`admission_number`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Student_Phone` (`admission_id`, `student_phone`) VALUES ('AMS110', '01900000000');

-- --------------------------------------------------------

--
-- Table structure for table `Enrollment`
--
CREATE TABLE `Enrollment` (
  `enrollment_id` varchar(50) NOT NULL,
  `enrollment_date` date NOT NULL,
  `enrollment_status` varchar(30) NOT NULL DEFAULT 'Enrolled',
  `admission_id` varchar(50) NOT NULL,
  `section_id` int(11) NOT NULL,
  PRIMARY KEY (`enrollment_id`),
  FOREIGN KEY (`admission_id`) REFERENCES `Student`(`admission_number`) ON DELETE CASCADE,
  FOREIGN KEY (`section_id`) REFERENCES `Course_Section`(`section_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `Enrollment`
INSERT INTO `Enrollment` (`enrollment_id`, `enrollment_date`, `enrollment_status`, `admission_id`, `section_id`) VALUES
('ENR20260323001', '2026-03-01', 'Enrolled', 'AMS110', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Attendance`
--
CREATE TABLE `Attendance` (
  `attendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `attendance_date` date NOT NULL,
  `attendance_time` time DEFAULT NULL,
  `attendance_status` varchar(20) NOT NULL,
  `enrollment_id` varchar(50) NOT NULL,
  `teacher_id` varchar(50) NOT NULL,
  `session_term_id` int(11) NOT NULL,
  PRIMARY KEY (`attendance_id`),
  FOREIGN KEY (`enrollment_id`) REFERENCES `Enrollment`(`enrollment_id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `Teacher`(`teacher_id`),
  FOREIGN KEY (`session_term_id`) REFERENCES `Session_Term`(`session_term_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;