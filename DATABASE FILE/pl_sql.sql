-- 1. Create Sequences for Auto-Incrementing IDs
CREATE SEQUENCE admin_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE city_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE term_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE session_term_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE course_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE course_section_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE attendance_seq START WITH 1 INCREMENT BY 1;

-- 2. Create Tables
CREATE TABLE Admin (
    admin_id NUMBER PRIMARY KEY,
    first_name VARCHAR2(50) NOT NULL,
    last_name VARCHAR2(50) NOT NULL,
    email_address VARCHAR2(100) NOT NULL,
    phone_number VARCHAR2(20),
    username VARCHAR2(50),
    password VARCHAR2(255) NOT NULL,
    account_created_date DATE NOT NULL,
    active_status VARCHAR2(20) DEFAULT 'Active'
);

CREATE TABLE Cities (
    city_id NUMBER PRIMARY KEY,
    city VARCHAR2(100) NOT NULL,
    country VARCHAR2(100) DEFAULT 'Unknown'
);

CREATE TABLE Term (
    term_id NUMBER PRIMARY KEY,
    term_name VARCHAR2(50) NOT NULL,
    admin_id NUMBER REFERENCES Admin(admin_id)
);

CREATE TABLE Session_Term (
    session_term_id NUMBER PRIMARY KEY,
    session_name VARCHAR2(50) NOT NULL,
    active_status VARCHAR2(20) DEFAULT 'Inactive',
    date_created DATE NOT NULL,
    term_id NUMBER REFERENCES Term(term_id),
    admin_id NUMBER REFERENCES Admin(admin_id)
);

CREATE TABLE Teacher (
    teacher_id VARCHAR2(50) PRIMARY KEY,
    first_name VARCHAR2(100) NOT NULL,
    last_name VARCHAR2(100) NOT NULL,
    tech_email VARCHAR2(100) NOT NULL,
    u_name VARCHAR2(50) NOT NULL,
    password VARCHAR2(255) NOT NULL,
    date_created DATE NOT NULL,
    active_status VARCHAR2(20) DEFAULT 'Active',
    admin_id NUMBER REFERENCES Admin(admin_id)
);

CREATE TABLE Teacher_Phone (
    teacher_id VARCHAR2(50) REFERENCES Teacher(teacher_id) ON DELETE CASCADE,
    teacher_phone VARCHAR2(20) NOT NULL,
    PRIMARY KEY (teacher_id, teacher_phone)
);

CREATE TABLE Course (
    course_id NUMBER PRIMARY KEY,
    course_name VARCHAR2(150) NOT NULL,
    course_code VARCHAR2(50) NOT NULL,
    credit_hours NUMBER DEFAULT 0,
    active_status VARCHAR2(20) DEFAULT 'Active',
    admin_id NUMBER REFERENCES Admin(admin_id)
);

CREATE TABLE Course_Section (
    section_id NUMBER PRIMARY KEY,
    section_name VARCHAR2(100) NOT NULL,
    assignment_status VARCHAR2(30) DEFAULT 'Unassigned',
    course_id NUMBER REFERENCES Course(course_id) ON DELETE CASCADE,
    teacher_id VARCHAR2(50) REFERENCES Teacher(teacher_id) ON DELETE SET NULL
);

CREATE TABLE Student (
    admission_number VARCHAR2(50) PRIMARY KEY,
    student_first_name VARCHAR2(100) NOT NULL,
    student_last_name VARCHAR2(100) NOT NULL,
    other_name VARCHAR2(100),
    gender VARCHAR2(20) NOT NULL,
    dob DATE NOT NULL,
    student_email VARCHAR2(100) NOT NULL,
    student_date_created DATE NOT NULL,
    student_active_status VARCHAR2(20) DEFAULT 'Active',
    admin_id NUMBER REFERENCES Admin(admin_id),
    city_id NUMBER REFERENCES Cities(city_id)
);

CREATE TABLE Student_Phone (
    admission_id VARCHAR2(50) REFERENCES Student(admission_number) ON DELETE CASCADE,
    student_phone VARCHAR2(20) NOT NULL,
    PRIMARY KEY (admission_id, student_phone)
);

CREATE TABLE Enrollment (
    enrollment_id VARCHAR2(50) PRIMARY KEY,
    enrollment_date DATE NOT NULL,
    enrollment_status VARCHAR2(30) DEFAULT 'Enrolled',
    admission_id VARCHAR2(50) REFERENCES Student(admission_number) ON DELETE CASCADE,
    section_id NUMBER REFERENCES Course_Section(section_id) ON DELETE CASCADE
);

CREATE TABLE Attendance (
    attendance_id NUMBER PRIMARY KEY,
    attendance_date DATE NOT NULL,
    attendance_time VARCHAR2(20),
    attendance_status VARCHAR2(20) NOT NULL,
    enrollment_id VARCHAR2(50) REFERENCES Enrollment(enrollment_id) ON DELETE CASCADE,
    teacher_id VARCHAR2(50) REFERENCES Teacher(teacher_id),
    session_term_id NUMBER REFERENCES Session_Term(session_term_id)
);

-- 3. Insert Sample Data
INSERT INTO Admin VALUES (admin_seq.NEXTVAL, 'Super', 'Admin', 'admin@mail.com', NULL, NULL, 'admin', TO_DATE('2026-01-01', 'YYYY-MM-DD'), 'Active');
INSERT INTO Cities VALUES (city_seq.NEXTVAL, 'Dhaka', 'Bangladesh');
INSERT INTO Term VALUES (term_seq.NEXTVAL, 'First Term', 1);
INSERT INTO Session_Term VALUES (session_term_seq.NEXTVAL, '2025/2026', 'Active', TO_DATE('2026-01-01', 'YYYY-MM-DD'), 1, 1);
INSERT INTO Teacher VALUES ('TCH001', 'John', 'Keroche', 'teacher@mail.com', 'jkeroche', 'pass123', TO_DATE('2026-03-01', 'YYYY-MM-DD'), 'Active', 1);
INSERT INTO Course VALUES (course_seq.NEXTVAL, 'Grade Nine', 'G9', 0, 'Active', 1);
INSERT INTO Course_Section VALUES (course_section_seq.NEXTVAL, 'N1', 'Assigned', 1, 'TCH001');

COMMIT;