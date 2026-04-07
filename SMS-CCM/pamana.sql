-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2026 at 06:40 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pamana`
--

-- --------------------------------------------------------

--
-- Table structure for table `admissions`
--

CREATE TABLE `admissions` (
  `admission_id` int(11) NOT NULL,
  `admission_type` enum('New Regular','Transferee','Returnee') NOT NULL,
  `is_working_student` tinyint(1) DEFAULT 0,
  `is_4ps_member` tinyint(1) DEFAULT 0,
  `program_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `year_level` int(11) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Enrolled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admissions`
--

INSERT INTO `admissions` (`admission_id`, `admission_type`, `is_working_student`, `is_4ps_member`, `program_id`, `branch_id`, `year_level`, `status`, `created_at`) VALUES
(1, 'Transferee', 0, 0, NULL, 1, 3, 'Pending', '2026-04-04 08:10:53');

-- --------------------------------------------------------

--
-- Table structure for table `admission_address`
--

CREATE TABLE `admission_address` (
  `address_id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `city_municipality` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission_address`
--

INSERT INTO `admission_address` (`address_id`, `admission_id`, `region`, `city_municipality`, `barangay`) VALUES
(1, 1, 'National Capital Region', 'Quezon City', 'Matandang Balara');

-- --------------------------------------------------------

--
-- Table structure for table `admission_education`
--

CREATE TABLE `admission_education` (
  `education_id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `education_level` enum('Primary','Secondary','Last School') NOT NULL,
  `school_name` varchar(150) DEFAULT NULL,
  `year_completed` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission_education`
--

INSERT INTO `admission_education` (`education_id`, `admission_id`, `education_level`, `school_name`, `year_completed`) VALUES
(1, 1, 'Primary', 'sdadadasdadadas', '2026-04-29'),
(2, 1, 'Secondary', 'dsadadaddasd', '2026-04-30'),
(3, 1, 'Last School', 'dsadaddada', '2026-04-21');

-- --------------------------------------------------------

--
-- Table structure for table `admission_family`
--

CREATE TABLE `admission_family` (
  `family_id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `relation` enum('Father','Mother','Guardian') NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission_family`
--

INSERT INTO `admission_family` (`family_id`, `admission_id`, `relation`, `last_name`, `first_name`, `middle_name`, `suffix`, `contact_number`, `occupation`) VALUES
(1, 1, 'Father', 'dasda', 'dadsa', 'dadas', 'dasdasda', NULL, NULL),
(2, 1, 'Mother', 'dasdas', 'dasdas', 'dasdsa', 'dasdasdas', NULL, NULL),
(3, 1, 'Father', 'dsadada', 'dsada', 'dasdada', 'dsada', '09999999999', 'dadsa');

-- --------------------------------------------------------

--
-- Table structure for table `admission_personal`
--

CREATE TABLE `admission_personal` (
  `personal_id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `civil_status` enum('Single','Married','Widowed') NOT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `birth_date` date NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `facebook_name` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission_personal`
--

INSERT INTO `admission_personal` (`personal_id`, `admission_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `sex`, `civil_status`, `religion`, `birth_date`, `email`, `contact_number`, `facebook_name`) VALUES
(1, 1, 'asdadas', 'dada', 'dasda', 'dsada', 'Male', 'Married', 'dsadada', '2008-12-17', 'adasda@gmail.com', '09999999999', 'sdadadasdasda');

-- --------------------------------------------------------

--
-- Table structure for table `admission_referrals`
--

CREATE TABLE `admission_referrals` (
  `referral_id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `referral_type` enum('Social Media','Adviser','Referral','Walk-in','None') DEFAULT NULL,
  `details` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission_referrals`
--

INSERT INTO `admission_referrals` (`referral_id`, `admission_id`, `referral_type`, `details`) VALUES
(1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

CREATE TABLE `applicants` (
  `applicant_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `application_reference` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `parent_name` varchar(255) DEFAULT NULL,
  `parent_contact` varchar(20) DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `program_id` int(11) NOT NULL,
  `year_level` varchar(50) DEFAULT NULL,
  `admission_type` enum('FRESHMAN','TRANSFEREE','RETURNING') NOT NULL DEFAULT 'FRESHMAN',
  `previous_school` varchar(255) DEFAULT NULL,
  `gpa_score` decimal(4,2) DEFAULT NULL,
  `entrance_exam_score` decimal(5,2) DEFAULT NULL,
  `application_status` enum('Submitted','For Review','Approved','Rejected','Paid','Enrolled') DEFAULT 'Submitted',
  `application_notes` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reviewed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_documents`
--

CREATE TABLE `application_documents` (
  `document_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_payments`
--

CREATE TABLE `application_payments` (
  `payment_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_status` enum('Pending','Completed','Failed','Refunded') DEFAULT 'Pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_requirements`
--

CREATE TABLE `application_requirements` (
  `requirement_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `requirement_type` varchar(100) DEFAULT NULL,
  `requirement_description` text DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_status_history`
--

CREATE TABLE `application_status_history` (
  `history_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `branch_id` int(11) NOT NULL,
  `branch_name` varchar(150) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`branch_id`, `branch_name`, `status`) VALUES
(1, '#1071 Brgy. Kaligayahan, Quirino Highway Novaliches, Quezon City', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `hire_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `program_id` int(11) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `status` enum('Pending','Enrolled','Cancelled') DEFAULT 'Pending',
  `admission_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_details`
--

CREATE TABLE `enrollment_details` (
  `detail_id` int(11) NOT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `status` enum('Active','Dropped','Completed') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `faculty_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grade_records`
--

CREATE TABLE `grade_records` (
  `grade_record_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `letter_grade` varchar(3) DEFAULT NULL,
  `gpa_points` decimal(3,2) DEFAULT NULL,
  `status` enum('Pending','Approved') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `interview_id` int(11) NOT NULL,
  `applicant_id` int(11) DEFAULT NULL,
  `score` decimal(3,2) DEFAULT NULL,
  `recommendation` enum('Pass','Fail','Maybe') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_postings`
--

CREATE TABLE `job_postings` (
  `job_id` int(11) NOT NULL,
  `position_id` int(11) DEFAULT NULL,
  `override_salary_min` decimal(12,2) DEFAULT NULL,
  `override_salary_max` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_id` int(11) NOT NULL,
  `position_name` varchar(150) DEFAULT NULL,
  `salary_min` decimal(12,2) DEFAULT NULL,
  `salary_max` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `program_id` int(11) NOT NULL,
  `program_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`program_id`, `program_name`) VALUES
(1, 'Bachelor of Science in Information Technology');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `day` varchar(20) DEFAULT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(100) DEFAULT NULL,
  `max_capacity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `section_name`, `max_capacity`) VALUES
(1, 'BSIT-1230', 40),
(2, 'BSIT-1330', 40),
(3, 'BSIT-1430', 40),
(4, 'BSIT-1530', 40),
(5, 'BSIT-1630', 40),
(6, 'BSIT-1730', 40);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `photo` longblob DEFAULT NULL,
  `signature` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_code` varchar(50) DEFAULT NULL,
  `subject_name` varchar(150) DEFAULT NULL,
  `units` int(11) DEFAULT NULL,
  `semester` varchar(100) DEFAULT NULL,
  `program` varchar(150) DEFAULT NULL,
  `year` varchar(50) DEFAULT NULL,
  `hours` int(11) DEFAULT NULL,
  `prerequisite` varchar(150) DEFAULT NULL,
  `corequisite` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_code`, `subject_name`, `units`, `semester`, `program`, `year`, `hours`, `prerequisite`, `corequisite`) VALUES
(1, 'CS101', 'Introduction to Programming', 3, '1st Semester', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subject_prerequisites`
--

CREATE TABLE `subject_prerequisites` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `prerequisite_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` enum('Admin','Registrar','Cashier','Student','HR') NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `email`, `role`, `status`, `created_at`) VALUES
(1, 'Admin', 'Admin123', 'mock-admin@gmail.com', 'Admin', 'Active', '2026-04-04 06:20:39'),
(2, 'Student1', 'Student1', 'mock-student@gmail.com', 'Student', 'Active', '2026-04-04 06:22:18'),
(3, 'Registrar1', 'Registrar1', 'mock-registrar@gmail.com', 'Registrar', 'Active', '2026-04-04 06:22:18'),
(4, 'Cashier1', 'Cashier1', 'mock-cashier@gmail.com', 'Cashier', 'Active', '2026-04-04 06:23:40'),
(5, 'HR1', 'HR1', 'mock-hr@gmail.com', 'HR', 'Active', '2026-04-04 06:24:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admissions`
--
ALTER TABLE `admissions`
  ADD PRIMARY KEY (`admission_id`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `admissions_ibfk_branch` (`branch_id`);

--
-- Indexes for table `admission_address`
--
ALTER TABLE `admission_address`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `admission_id` (`admission_id`);

--
-- Indexes for table `admission_education`
--
ALTER TABLE `admission_education`
  ADD PRIMARY KEY (`education_id`),
  ADD KEY `admission_id` (`admission_id`);

--
-- Indexes for table `admission_family`
--
ALTER TABLE `admission_family`
  ADD PRIMARY KEY (`family_id`),
  ADD KEY `admission_id` (`admission_id`);

--
-- Indexes for table `admission_personal`
--
ALTER TABLE `admission_personal`
  ADD PRIMARY KEY (`personal_id`),
  ADD KEY `admission_id` (`admission_id`);

--
-- Indexes for table `admission_referrals`
--
ALTER TABLE `admission_referrals`
  ADD PRIMARY KEY (`referral_id`),
  ADD KEY `admission_id` (`admission_id`);

--
-- Indexes for table `applicants`
--
ALTER TABLE `applicants`
  ADD PRIMARY KEY (`applicant_id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD UNIQUE KEY `application_reference` (`application_reference`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_program` (`program_id`),
  ADD KEY `idx_status` (`application_status`),
  ADD KEY `idx_reference` (`application_reference`),
  ADD KEY `idx_submitted` (`submitted_at`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_applications_program` (`program_id`),
  ADD KEY `idx_applications_year` (`year_level`),
  ADD KEY `idx_applications_admission_type` (`admission_type`),
  ADD KEY `idx_applications_reference` (`application_reference`);

--
-- Indexes for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `idx_application` (`application_id`);

--
-- Indexes for table `application_payments`
--
ALTER TABLE `application_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `payment_reference` (`payment_reference`),
  ADD KEY `idx_application` (`application_id`),
  ADD KEY `idx_status` (`payment_status`);

--
-- Indexes for table `application_requirements`
--
ALTER TABLE `application_requirements`
  ADD PRIMARY KEY (`requirement_id`),
  ADD KEY `idx_application` (`application_id`);

--
-- Indexes for table `application_status_history`
--
ALTER TABLE `application_status_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `idx_application` (`application_id`),
  ADD KEY `idx_changed_at` (`changed_at`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`branch_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `idx_student_enroll` (`student_id`,`program_id`),
  ADD KEY `fk_enrollment_admission` (`admission_id`);

--
-- Indexes for table `enrollment_details`
--
ALTER TABLE `enrollment_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `enrollment_id` (`enrollment_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- Indexes for table `grade_records`
--
ALTER TABLE `grade_records`
  ADD PRIMARY KEY (`grade_record_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `idx_grade_lookup` (`student_id`,`schedule_id`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`interview_id`),
  ADD KEY `applicant_id` (`applicant_id`);

--
-- Indexes for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_payment_lookup` (`student_id`,`payment_date`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`program_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`),
  ADD UNIQUE KEY `subject_code_2` (`subject_code`);

--
-- Indexes for table `subject_prerequisites`
--
ALTER TABLE `subject_prerequisites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `prerequisite_id` (`prerequisite_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admissions`
--
ALTER TABLE `admissions`
  MODIFY `admission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admission_address`
--
ALTER TABLE `admission_address`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admission_education`
--
ALTER TABLE `admission_education`
  MODIFY `education_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admission_family`
--
ALTER TABLE `admission_family`
  MODIFY `family_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admission_personal`
--
ALTER TABLE `admission_personal`
  MODIFY `personal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admission_referrals`
--
ALTER TABLE `admission_referrals`
  MODIFY `referral_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `applicants`
--
ALTER TABLE `applicants`
  MODIFY `applicant_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `application_documents`
--
ALTER TABLE `application_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `application_payments`
--
ALTER TABLE `application_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `application_requirements`
--
ALTER TABLE `application_requirements`
  MODIFY `requirement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `application_status_history`
--
ALTER TABLE `application_status_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment_details`
--
ALTER TABLE `enrollment_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grade_records`
--
ALTER TABLE `grade_records`
  MODIFY `grade_record_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `interview_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_postings`
--
ALTER TABLE `job_postings`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subject_prerequisites`
--
ALTER TABLE `subject_prerequisites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admissions`
--
ALTER TABLE `admissions`
  ADD CONSTRAINT `admissions_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`),
  ADD CONSTRAINT `admissions_ibfk_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`);

--
-- Constraints for table `admission_address`
--
ALTER TABLE `admission_address`
  ADD CONSTRAINT `admission_address_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`admission_id`) ON DELETE CASCADE;

--
-- Constraints for table `admission_education`
--
ALTER TABLE `admission_education`
  ADD CONSTRAINT `admission_education_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`admission_id`) ON DELETE CASCADE;

--
-- Constraints for table `admission_family`
--
ALTER TABLE `admission_family`
  ADD CONSTRAINT `admission_family_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`admission_id`) ON DELETE CASCADE;

--
-- Constraints for table `admission_personal`
--
ALTER TABLE `admission_personal`
  ADD CONSTRAINT `admission_personal_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`admission_id`) ON DELETE CASCADE;

--
-- Constraints for table `admission_referrals`
--
ALTER TABLE `admission_referrals`
  ADD CONSTRAINT `admission_referrals_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`admission_id`) ON DELETE CASCADE;

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `applications_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD CONSTRAINT `application_documents_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `application_payments`
--
ALTER TABLE `application_payments`
  ADD CONSTRAINT `application_payments_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `application_requirements`
--
ALTER TABLE `application_requirements`
  ADD CONSTRAINT `application_requirements_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `application_status_history`
--
ALTER TABLE `application_status_history`
  ADD CONSTRAINT `application_status_history_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`),
  ADD CONSTRAINT `fk_enrollment_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`admission_id`);

--
-- Constraints for table `enrollment_details`
--
ALTER TABLE `enrollment_details`
  ADD CONSTRAINT `enrollment_details_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollment_details_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`);

--
-- Constraints for table `faculty`
--
ALTER TABLE `faculty`
  ADD CONSTRAINT `faculty_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `grade_records`
--
ALTER TABLE `grade_records`
  ADD CONSTRAINT `grade_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `grade_records_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`);

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `interviews_ibfk_1` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD CONSTRAINT `job_postings_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`),
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`),
  ADD CONSTRAINT `schedules_ibfk_3` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `subject_prerequisites`
--
ALTER TABLE `subject_prerequisites`
  ADD CONSTRAINT `subject_prerequisites_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_prerequisites_ibfk_2` FOREIGN KEY (`prerequisite_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
