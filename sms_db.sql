-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 06, 2026 at 07:41 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `applicant_address`
--

CREATE TABLE `applicant_address` (
  `application_id` int(11) NOT NULL,
  `region` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicant_address`
--

INSERT INTO `applicant_address` (`application_id`, `region`, `city`, `barangay`, `address`) VALUES
(6, 'Region I', 'Bangui', 'Abaca', 'Test'),
(7, 'Region V', 'Rapu-Rapu', 'Malobago', 'Test'),
(8, 'Region XI', 'Hagonoy', 'Sacub', 'Test2'),
(9, 'National Capital Region', 'Quezon City', 'Payatas', 'Phase 3 Block 7 Lupang Pangako, Payatas B, Quezon City'),
(10, 'National Capital Region', 'Quezon City', 'Payatas', 'test');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_contact`
--

CREATE TABLE `applicant_contact` (
  `application_id` int(11) NOT NULL,
  `email_address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `fbname` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicant_contact`
--

INSERT INTO `applicant_contact` (`application_id`, `email_address`, `contact_number`, `fbname`) VALUES
(6, 'jeonrious@gmail.com', '09999999999', 'Test'),
(7, 'kookie.jeon.danel.97@gmail.com', '09674801002', 'Test'),
(8, 'Test2@gmail.com', '09333333333', 'Test2'),
(9, 'angelitobruzon222@gmail.com', '09456987778', 'Jake Buenaventura'),
(10, 'naomikatagaki222@gmail.com', '09797989798', 'Angelito Bruzon');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_education`
--

CREATE TABLE `applicant_education` (
  `application_id` int(11) NOT NULL,
  `primary_school` varchar(255) DEFAULT NULL,
  `primary_graduation_date` varchar(7) DEFAULT NULL,
  `secondary_school` varchar(255) DEFAULT NULL,
  `secondary_graduation_date` varchar(7) DEFAULT NULL,
  `last_school_attended` varchar(255) DEFAULT NULL,
  `last_school_year_attended` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicant_education`
--

INSERT INTO `applicant_education` (`application_id`, `primary_school`, `primary_graduation_date`, `secondary_school`, `secondary_graduation_date`, `last_school_attended`, `last_school_year_attended`) VALUES
(6, 'Test', '2023-01', 'Test', '2024-06', 'Test', '2017-06'),
(7, 'Test', '2021-02', 'Test', '2024-02', 'Test', '2026-02'),
(8, 'Test2', '2013-01', 'Test2', '2018-02', 'Test2', '2022-06'),
(9, 'test', '2021-02', 'test', '2022-02', 'test', '2025-07'),
(10, 'test', '2019-02', 'test', '2021-02', 'test', '2023-02');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_family`
--

CREATE TABLE `applicant_family` (
  `application_id` int(11) NOT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `guardian_contact` varchar(50) DEFAULT NULL,
  `guardian_relation` enum('Father','Mother','Guardian') DEFAULT NULL,
  `guardian_occupation` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicant_family`
--

INSERT INTO `applicant_family` (`application_id`, `father_name`, `mother_name`, `guardian_name`, `guardian_contact`, `guardian_relation`, `guardian_occupation`) VALUES
(6, 'Test Test Test', 'Test Test Test', 'Test Test Test', '09111111111', 'Mother', 'Test'),
(7, 'Test Test Test', 'Test Test Test', 'Test Test Test Test', '09222222222', 'Mother', 'Test'),
(8, 'Test2 Test2 Test2', 'Test2 Test2 Test2', 'Test2 Test2 Test2', '09755894938', 'Guardian', 'Test2'),
(9, 'test test test', 'test test tes', 'test test test', '09458796548', 'Father', 'N/A'),
(10, 'test test Mario', 'test test test', 'test test Mario', '09458796548', 'Father', 'N/A');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_personal_info`
--

CREATE TABLE `applicant_personal_info` (
  `application_id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `sex` enum('Male','Female') DEFAULT NULL,
  `civil_status` enum('Single','Married','Widowed','Separated') DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `religion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicant_personal_info`
--

INSERT INTO `applicant_personal_info` (`application_id`, `first_name`, `middle_name`, `last_name`, `suffix`, `sex`, `civil_status`, `birthdate`, `religion`) VALUES
(6, 'Test', 'Test', 'Test', NULL, 'Male', 'Married', '2008-12-04', 'Test'),
(7, 'Test1', 'Test1', 'Test1', NULL, 'Male', 'Widowed', '2008-12-30', 'Test'),
(8, 'Test2', 'Test2', 'Test2', NULL, 'Female', 'Widowed', '2008-12-21', 'Test2'),
(9, 'Jake', 'Mananalsal', 'Buenaventura', NULL, 'Male', 'Single', '2005-08-26', 'Roman Catholic'),
(10, 'Angelito', 'Igong-Igong', 'Bruzon', NULL, 'Male', 'Single', '2005-07-06', 'Roman Catholic');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `selection_id` int(11) DEFAULT NULL,
  `admission_type` enum('New Regular','Transferee','Returnee') DEFAULT NULL,
  `is_working` tinyint(1) DEFAULT 0,
  `is_4ps` int(11) DEFAULT 0,
  `status` enum('Pending','Validated','Enrolled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`application_id`, `selection_id`, `admission_type`, `is_working`, `is_4ps`, `status`, `created_at`, `reference_id`) VALUES
(6, 1, 'New Regular', 0, 0, 'Pending', '2026-04-05 07:03:52', 1),
(7, 4, 'Transferee', 1, 0, 'Validated', '2026-04-05 07:15:53', 2),
(8, 3, 'Returnee', 0, 1, 'Validated', '2026-04-05 09:17:53', 3),
(9, 1, 'New Regular', 0, 0, 'Enrolled', '2026-04-05 17:42:27', 4),
(10, 1, 'New Regular', 0, 0, 'Pending', '2026-04-06 15:53:05', 5);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(100) DEFAULT NULL,
  `course_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `course_code`) VALUES
(1, 'Bachelor of Science in Information Technology', 'BSIT');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `term_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Validated','Paid','Enrolled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `term_id`, `status`, `created_at`) VALUES
(1, 8, 1, 'Enrolled', '2026-04-06 16:49:29');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_details`
--

CREATE TABLE `enrollment_details` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `section_subject_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `faculty_id` int(11) NOT NULL,
  `faculty_code` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'FK → users.user_id (role_id=5 Faculty)',
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `designation` varchar(50) DEFAULT 'Instructor',
  `type` enum('Full-Time','Part-Time') DEFAULT 'Full-Time',
  `max_units` int(11) NOT NULL DEFAULT 21,
  `email` varchar(100) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`faculty_id`, `faculty_code`, `user_id`, `first_name`, `last_name`, `designation`, `type`, `max_units`, `email`, `specialization`, `status`, `created_at`) VALUES
(1, 'FAC-1001', 6, 'Miguel', 'Santos', 'Associate Professor', 'Full-Time', 21, 'm.santos@school.edu', 'Database Systems, Web Development', 'Active', '2026-04-05 06:17:46'),
(2, 'FAC-1002', 7, 'Maria', 'Reyes', 'Professor', 'Full-Time', 21, 'm.reyes@school.edu', 'Software Engineering, OOP', 'Active', '2026-04-05 06:17:46'),
(3, 'FAC-1003', 8, 'Jose', 'Cruz', 'Assistant Professor', 'Full-Time', 21, 'j.cruz@school.edu', 'Networking, System Administration', 'Active', '2026-04-05 06:17:46'),
(4, 'FAC-1004', 9, 'Ana', 'Garcia', 'Associate Professor', 'Full-Time', 21, 'a.garcia@school.edu', 'Programming, Algorithms', 'Active', '2026-04-05 06:17:46'),
(5, 'FAC-1005', 10, 'Patricia', 'Lim', 'Instructor', 'Full-Time', 21, 'p.lim@school.edu', 'Mathematics, Statistics', 'Active', '2026-04-05 06:17:46'),
(6, 'FAC-1006', 11, 'Roberto', 'Tan', 'Instructor', 'Part-Time', 12, 'r.tan@school.edu', 'Computer Architecture, OS', 'Active', '2026-04-05 06:17:46'),
(7, 'FAC-1007', 12, 'Carmela', 'Reyes', 'Assistant Professor', 'Full-Time', 21, 'c.reyes@school.edu', 'HCI, Systems Analysis', 'Active', '2026-04-05 06:17:46'),
(8, 'FAC-1008', 13, 'Dionisio', 'Bautista', 'Instructor', 'Part-Time', 12, 'd.bautista@school.edu', 'Programming, Data Structures', 'Active', '2026-04-05 06:17:46');

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `fee_id` int(11) NOT NULL,
  `fee_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`fee_id`, `fee_name`, `amount`, `is_active`) VALUES
(1, 'Laboratory Fees', 2000.00, 1),
(2, 'Miscellaneous Fees', 500.00, 1),
(3, 'Tuition Fee', 4000.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `term_id` int(11) DEFAULT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `status` enum('Encoded','Submitted','Approved','Released') DEFAULT 'Encoded'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('Partial','Full') NOT NULL,
  `payment_method` enum('Cash') DEFAULT NULL,
  `receipt_number` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference_id` int(11) DEFAULT NULL,
  `fee_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `enrollment_id`, `student_id`, `amount`, `payment_status`, `payment_method`, `receipt_number`, `payment_date`, `reference_id`, `fee_id`) VALUES
(1, NULL, NULL, 8850.00, 'Full', 'Cash', 'RCP-20260405194751-Y3NVTX', '2026-04-05 17:47:51', 4, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reference_numbers`
--

CREATE TABLE `reference_numbers` (
  `reference_id` int(11) NOT NULL,
  `reference_number` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reference_numbers`
--

INSERT INTO `reference_numbers` (`reference_id`, `reference_number`, `created_at`) VALUES
(1, 'SMS-XYZ1A234', '2026-04-05 07:27:51'),
(2, 'SMS-CDE1A234', '2026-04-05 07:27:51'),
(3, 'SMS-NPC7C253', '2026-04-05 09:17:53'),
(4, 'SMS-AAW2I108', '2026-04-05 17:42:27'),
(5, 'SMS-QDZ0Z497', '2026-04-06 15:53:05');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(2, 'Admin'),
(3, 'Cashier'),
(5, 'Faculty'),
(4, 'Registrar'),
(1, 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(25) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `day` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `section_name`, `subject_id`, `teacher_id`, `room`, `day`, `start_time`, `end_time`, `capacity`) VALUES
(1, 'BSIT-2001', 1, 5, '1001', 'Morning', '07:00:00', '12:00:00', 40),
(2, 'BSIT-2002', 2, 5, '1002', 'Afternoon', '12:00:00', '17:00:00', 40),
(3, 'BSIT - 11020', 8, NULL, NULL, NULL, NULL, NULL, 40);

-- --------------------------------------------------------

--
-- Table structure for table `section_subjects`
--

CREATE TABLE `section_subjects` (
  `id` int(11) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `day` enum('Mon','Tue','Wed','Thu','Fri','Sat') DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section_subjects`
--

INSERT INTO `section_subjects` (`id`, `section_id`, `subject_id`, `teacher_id`, `day`, `start_time`, `end_time`, `room`) VALUES
(1, 3, 8, NULL, NULL, NULL, NULL, NULL),
(2, 3, 9, NULL, NULL, NULL, NULL, NULL),
(3, 3, 10, NULL, NULL, NULL, NULL, NULL),
(4, 3, 11, NULL, NULL, NULL, NULL, NULL),
(5, 3, 12, NULL, NULL, NULL, NULL, NULL),
(6, 3, 13, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `selection`
--

CREATE TABLE `selection` (
  `selection_id` int(11) NOT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `year_level` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `selection`
--

INSERT INTO `selection` (`selection_id`, `branch`, `course_id`, `year_level`) VALUES
(1, 'Main Branch', 1, 1),
(2, 'Main Branch', 1, 2),
(3, 'Main Branch', 1, 3),
(4, 'Main Branch', 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `student_number` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('Applicant','Active','On_Leave','Dropped','Graduated','Irregular') DEFAULT 'Applicant',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `application_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_number`, `user_id`, `status`, `created_at`, `application_id`) VALUES
(1, '260000001', 1, 'Applicant', '2026-04-05 05:02:45', NULL),
(2, '260000002', 1, 'Active', '2026-04-05 05:03:21', NULL),
(3, '260000003', 1, 'On_Leave', '2026-04-05 05:03:21', NULL),
(4, '260000004', 1, 'Dropped', '2026-04-05 05:04:01', NULL),
(5, '260000005', 1, 'Graduated', '2026-04-05 05:04:01', NULL),
(6, '260000006', 1, 'Irregular', '2026-04-05 05:06:22', NULL),
(7, '26000007', NULL, 'Applicant', '2026-04-05 09:17:53', NULL),
(8, '260000008', 6, 'Applicant', '2026-04-06 16:49:29', 10);

-- --------------------------------------------------------

--
-- Table structure for table `student_discounts`
--

CREATE TABLE `student_discounts` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `discount_type` varchar(100) DEFAULT NULL,
  `discount_category` enum('Percentage (%)','Fixed Amount') DEFAULT NULL,
  `discount_value` decimal(10,2) DEFAULT NULL,
  `scholarship_name` varchar(255) DEFAULT NULL,
  `original_assessment` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `net_assessment` decimal(10,2) DEFAULT 0.00,
  `supporting_documents` longblob DEFAULT NULL,
  `status` enum('Pending','Approved') DEFAULT 'Pending',
  `has_penalty` tinyint(1) DEFAULT 0,
  `penalty_amount` decimal(10,2) DEFAULT 500.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_code` varchar(50) DEFAULT NULL,
  `subject_name` varchar(255) DEFAULT NULL,
  `units` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `year_level` int(11) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_code`, `subject_name`, `units`, `price`, `year_level`, `semester`) VALUES
(1, 'GE1', 'Understanding the Self', 3, 350.00, 1, 1),
(2, 'GE2', 'Purposive Communication', 3, 350.00, 1, 1),
(3, 'ITP101', 'Introduction to Computing', 3, 350.00, 1, 1),
(4, 'ITP102', 'Computer Programming 1', 3, 350.00, 1, 1),
(5, 'MATH1', 'Mathematics in the Modern World', 3, 350.00, 1, 1),
(6, 'NSTP1', 'National Service Training Program 1', 3, 350.00, 1, 1),
(7, 'PE1', 'Self-Testing Activities', 2, 250.00, 1, 1),
(8, 'GE3', 'The Contemporary World', 3, 350.00, 1, 2),
(9, 'GE4', 'Readings in Philippine History', 3, 350.00, 1, 2),
(10, 'ITP103', 'Computer Programming 2', 3, 350.00, 1, 2),
(11, 'ITP104', 'Data Structures and Algorithms', 3, 350.00, 1, 2),
(12, 'NSTP2', 'National Service Training Program 2', 3, 350.00, 1, 2),
(13, 'PE2', 'Fundamentals of Martial Arts', 2, 250.00, 1, 2),
(14, 'GE5', 'Art Appreciation', 3, 350.00, 2, 1),
(15, 'GE6', 'Ethics', 3, 350.00, 2, 1),
(16, 'ITP201', 'Information Management', 3, 350.00, 2, 1),
(17, 'ITP202', 'Networking 1', 3, 350.00, 2, 1),
(18, 'ITP203', 'Object Oriented Programming', 3, 350.00, 2, 1),
(19, 'PE3', 'Individual and Dual Sports', 2, 250.00, 2, 1),
(20, 'GE7', 'Science, Technology, and Society', 3, 350.00, 2, 2),
(21, 'GE8', 'The Life and Works of Rizal', 3, 350.00, 2, 2),
(22, 'ITP204', 'Web Systems and Technologies', 3, 350.00, 2, 2),
(23, 'ITP205', 'Systems Analysis and Design', 3, 350.00, 2, 2),
(24, 'ITP206', 'Human Computer Interaction', 3, 350.00, 2, 2),
(25, 'PE4', 'Team Sports', 2, 250.00, 2, 2),
(26, 'GE9', 'Pantilikan', 3, 350.00, 3, 1),
(27, 'ITP301', 'Application Development & Emerging Tech', 3, 350.00, 3, 1),
(28, 'ITP302', 'Social and Professional Issues in IT', 3, 350.00, 3, 1),
(29, 'ITP303', 'Capstone Project 1', 3, 350.00, 3, 1),
(30, 'ITP304', 'Information Assurance and Security 1', 3, 350.00, 3, 1),
(31, 'ITP305', 'Information Assurance and Security 2', 3, 350.00, 3, 2),
(32, 'ITP306', 'Mobile Computing', 3, 250.00, 3, 2),
(33, 'ITP307', 'Quantitative Methods', 3, 350.00, 3, 2),
(34, 'ITP308', 'Networking 2', 3, 350.00, 3, 2),
(35, 'ITEL1', 'IT Elective 1', 3, 350.00, 4, 1),
(36, 'ITP401', 'Capstone Project 2', 3, 350.00, 4, 1),
(37, 'ITP402', 'Systems Administration and Maintenance', 3, 350.00, 4, 1),
(38, 'ITP403', 'Platform Technologies', 3, 350.00, 4, 1),
(39, 'ITP404', 'Practicum (Internship - 486 Hours)', 6, 350.00, 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `subject_prerequisite`
--

CREATE TABLE `subject_prerequisite` (
  `subject_prerequisite_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `prerequisite_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject_prerequisite`
--

INSERT INTO `subject_prerequisite` (`subject_prerequisite_id`, `subject_id`, `prerequisite_id`) VALUES
(1, 10, 4),
(2, 11, 4),
(3, 12, 6),
(4, 13, 7),
(5, 16, 10),
(6, 17, 3),
(7, 18, 10),
(8, 19, 13),
(9, 22, 18),
(10, 23, 16),
(11, 24, 18),
(12, 25, 19),
(13, 27, 22),
(14, 29, 23),
(15, 30, 17),
(16, 31, 30),
(17, 32, 22),
(18, 33, 5),
(19, 34, 17),
(20, 36, 29),
(21, 37, 34),
(22, 38, 34),
(23, 39, 36);

-- --------------------------------------------------------

--
-- Table structure for table `subject_preselection`
--

CREATE TABLE `subject_preselection` (
  `preselection_id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `term_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `validated_by` int(11) DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject_preselection`
--

INSERT INTO `subject_preselection` (`preselection_id`, `application_id`, `term_id`, `status`, `submitted_at`, `validated_by`, `validated_at`) VALUES
(1, 7, 1, 'Approved', '2026-04-05 09:17:53', 4, '2026-04-05 09:57:14'),
(2, 9, 1, 'Approved', '2026-04-05 17:42:27', 4, '2026-04-05 17:44:01'),
(3, 10, 1, 'Pending', '2026-04-06 15:53:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subject_preselection_details`
--

CREATE TABLE `subject_preselection_details` (
  `id` int(11) NOT NULL,
  `preselection_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject_preselection_details`
--

INSERT INTO `subject_preselection_details` (`id`, `preselection_id`, `subject_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 2, 1),
(4, 2, 2),
(5, 2, 3),
(6, 2, 4),
(7, 2, 5),
(8, 2, 6),
(9, 2, 7),
(10, 3, 1),
(11, 3, 2),
(12, 3, 3),
(13, 3, 4),
(14, 3, 5),
(15, 3, 6),
(16, 3, 7);

-- --------------------------------------------------------

--
-- Table structure for table `terms`
--

CREATE TABLE `terms` (
  `term_id` int(11) NOT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `terms`
--

INSERT INTO `terms` (`term_id`, `school_year`, `semester`) VALUES
(1, '2026-2027', 1),
(2, '2026-2027', 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `role_id`, `is_active`, `created_at`) VALUES
(1, 'Student1', 'Student1', 1, 1, '2026-04-04 20:00:45'),
(2, 'Admin1', 'Admin1', 2, 1, '2026-04-04 20:00:45'),
(3, 'Cashier1', 'Cashier1', 3, 1, '2026-04-04 20:01:17'),
(4, 'Registrar1', '$2y$10$2NUeYPjvQqUcDwmpz9aZn.MW.1UqFdzLCYYM.ju.qzAeCFMZf10K.', 4, 1, '2026-04-04 20:01:17'),
(5, 'Faculty1', 'Faculty1', 5, 1, '2026-04-05 05:12:39'),
(6, '260000008', '260000008', 1, 1, '2026-04-06 16:49:29');

-- --------------------------------------------------------

--
-- Table structure for table `website_contact_submissions`
--

CREATE TABLE `website_contact_submissions` (
  `id` int(11) NOT NULL,
  `sender_name` varchar(150) NOT NULL,
  `sender_email` varchar(150) NOT NULL,
  `subject` varchar(180) NOT NULL,
  `message` text NOT NULL,
  `status` enum('New','Read','Resolved') NOT NULL DEFAULT 'New',
  `submitted_at` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `website_enrollment_submissions`
--

CREATE TABLE `website_enrollment_submissions` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `program` varchar(120) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('Pending','Validated','Enrolled') NOT NULL DEFAULT 'Pending',
  `submitted_at` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applicant_address`
--
ALTER TABLE `applicant_address`
  ADD PRIMARY KEY (`application_id`);

--
-- Indexes for table `applicant_contact`
--
ALTER TABLE `applicant_contact`
  ADD PRIMARY KEY (`application_id`);

--
-- Indexes for table `applicant_education`
--
ALTER TABLE `applicant_education`
  ADD PRIMARY KEY (`application_id`);

--
-- Indexes for table `applicant_family`
--
ALTER TABLE `applicant_family`
  ADD PRIMARY KEY (`application_id`);

--
-- Indexes for table `applicant_personal_info`
--
ALTER TABLE `applicant_personal_info`
  ADD PRIMARY KEY (`application_id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `selection_id` (`selection_id`),
  ADD KEY `reference_id` (`reference_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `term_id` (`term_id`);

--
-- Indexes for table `enrollment_details`
--
ALTER TABLE `enrollment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enrollment_id` (`enrollment_id`),
  ADD KEY `section_subject_id` (`section_subject_id`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`fee_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`grade_id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`section_id`,`term_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `term_id` (`term_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `enrollment_id` (`enrollment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `reference_id` (`reference_id`),
  ADD KEY `fk_payments_fee` (`fee_id`);

--
-- Indexes for table `reference_numbers`
--
ALTER TABLE `reference_numbers`
  ADD PRIMARY KEY (`reference_id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `fk_sections_subject` (`subject_id`);

--
-- Indexes for table `section_subjects`
--
ALTER TABLE `section_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `selection`
--
ALTER TABLE `selection`
  ADD PRIMARY KEY (`selection_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_student_application` (`application_id`);

--
-- Indexes for table `student_discounts`
--
ALTER TABLE `student_discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_discount_student` (`student_id`),
  ADD KEY `fk_discount_term` (`term_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `subject_prerequisite`
--
ALTER TABLE `subject_prerequisite`
  ADD PRIMARY KEY (`subject_prerequisite_id`),
  ADD KEY `fk_main_subject` (`subject_id`),
  ADD KEY `fk_required_prerequisite` (`prerequisite_id`);

--
-- Indexes for table `subject_preselection`
--
ALTER TABLE `subject_preselection`
  ADD PRIMARY KEY (`preselection_id`),
  ADD KEY `term_id` (`term_id`),
  ADD KEY `validated_by` (`validated_by`),
  ADD KEY `subject_preselection_ibfk_1` (`application_id`);

--
-- Indexes for table `subject_preselection_details`
--
ALTER TABLE `subject_preselection_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `preselection_id` (`preselection_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `terms`
--
ALTER TABLE `terms`
  ADD PRIMARY KEY (`term_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `website_contact_submissions`
--
ALTER TABLE `website_contact_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_website_contact_status` (`status`),
  ADD KEY `idx_website_contact_submitted_at` (`submitted_at`);

--
-- Indexes for table `website_enrollment_submissions`
--
ALTER TABLE `website_enrollment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_website_enrollment_status` (`status`),
  ADD KEY `idx_website_enrollment_program` (`program`),
  ADD KEY `idx_website_enrollment_submitted_at` (`submitted_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `enrollment_details`
--
ALTER TABLE `enrollment_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reference_numbers`
--
ALTER TABLE `reference_numbers`
  MODIFY `reference_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `section_subjects`
--
ALTER TABLE `section_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `selection`
--
ALTER TABLE `selection`
  MODIFY `selection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `student_discounts`
--
ALTER TABLE `student_discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `subject_prerequisite`
--
ALTER TABLE `subject_prerequisite`
  MODIFY `subject_prerequisite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `subject_preselection`
--
ALTER TABLE `subject_preselection`
  MODIFY `preselection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subject_preselection_details`
--
ALTER TABLE `subject_preselection_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `terms`
--
ALTER TABLE `terms`
  MODIFY `term_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `website_contact_submissions`
--
ALTER TABLE `website_contact_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `website_enrollment_submissions`
--
ALTER TABLE `website_enrollment_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applicant_address`
--
ALTER TABLE `applicant_address`
  ADD CONSTRAINT `applicant_address_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`);

--
-- Constraints for table `applicant_contact`
--
ALTER TABLE `applicant_contact`
  ADD CONSTRAINT `applicant_contact_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`);

--
-- Constraints for table `applicant_education`
--
ALTER TABLE `applicant_education`
  ADD CONSTRAINT `applicant_education_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`);

--
-- Constraints for table `applicant_family`
--
ALTER TABLE `applicant_family`
  ADD CONSTRAINT `applicant_family_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`);

--
-- Constraints for table `applicant_personal_info`
--
ALTER TABLE `applicant_personal_info`
  ADD CONSTRAINT `applicant_personal_info_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`);

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`selection_id`) REFERENCES `selection` (`selection_id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`reference_id`) REFERENCES `reference_numbers` (`reference_id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`term_id`) REFERENCES `terms` (`term_id`);

--
-- Constraints for table `enrollment_details`
--
ALTER TABLE `enrollment_details`
  ADD CONSTRAINT `enrollment_details_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`),
  ADD CONSTRAINT `enrollment_details_ibfk_2` FOREIGN KEY (`section_subject_id`) REFERENCES `section_subjects` (`id`);

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`),
  ADD CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`term_id`) REFERENCES `terms` (`term_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_fee` FOREIGN KEY (`fee_id`) REFERENCES `fees` (`fee_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`reference_id`) REFERENCES `reference_numbers` (`reference_id`);

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `fk_sections_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sections_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `section_subjects`
--
ALTER TABLE `section_subjects`
  ADD CONSTRAINT `section_subjects_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`),
  ADD CONSTRAINT `section_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`),
  ADD CONSTRAINT `section_subjects_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `selection`
--
ALTER TABLE `selection`
  ADD CONSTRAINT `selection_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_student_application` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`),
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `student_discounts`
--
ALTER TABLE `student_discounts`
  ADD CONSTRAINT `fk_discount_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `fk_discount_term` FOREIGN KEY (`term_id`) REFERENCES `terms` (`term_id`);

--
-- Constraints for table `subject_prerequisite`
--
ALTER TABLE `subject_prerequisite`
  ADD CONSTRAINT `fk_main_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_required_prerequisite` FOREIGN KEY (`prerequisite_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `subject_preselection`
--
ALTER TABLE `subject_preselection`
  ADD CONSTRAINT `subject_preselection_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`),
  ADD CONSTRAINT `subject_preselection_ibfk_2` FOREIGN KEY (`term_id`) REFERENCES `terms` (`term_id`),
  ADD CONSTRAINT `subject_preselection_ibfk_3` FOREIGN KEY (`validated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `subject_preselection_details`
--
ALTER TABLE `subject_preselection_details`
  ADD CONSTRAINT `subject_preselection_details_ibfk_1` FOREIGN KEY (`preselection_id`) REFERENCES `subject_preselection` (`preselection_id`),
  ADD CONSTRAINT `subject_preselection_details_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
