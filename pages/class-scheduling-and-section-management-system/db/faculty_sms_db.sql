-- ============================================================
-- FILE: db/faculty_sms_db.sql
-- Adds the faculty table to sms_db.
-- user_id references users.user_id WHERE role_id = 5 (Faculty)
-- ============================================================

USE `sms_db`;

-- ── Create faculty table ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `faculty` (
  `faculty_id`     int(11) NOT NULL AUTO_INCREMENT,
  `faculty_code`   varchar(20) NOT NULL,
  `user_id`        int(11) NOT NULL COMMENT 'FK → users.user_id (role_id=5 Faculty)',
  `first_name`     varchar(50) NOT NULL,
  `last_name`      varchar(50) NOT NULL,
  `designation`    varchar(50) DEFAULT 'Instructor',
  `type`           enum('Full-Time','Part-Time') DEFAULT 'Full-Time',
  `max_units`      int(11) NOT NULL DEFAULT 21,
  `email`          varchar(100) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `status`         enum('Active','Inactive') DEFAULT 'Active',
  `created_at`     timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`faculty_id`),
  UNIQUE KEY `faculty_code` (`faculty_code`),
  KEY `fk_faculty_user` (`user_id`),
  CONSTRAINT `fk_faculty_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Insert 8 faculty records ────────────────────────────────
-- Each user_id must already exist in users with role_id = 5 (Faculty).
-- The INSERT below adds users 6–13 (if not yet present) then faculty rows.

-- Add faculty users (role_id = 5) if they don't already exist
INSERT IGNORE INTO `users`
    (`username`, `password_hash`, `role_id`, `is_active`)
VALUES
    ('FAC-1001', '$2y$10$placeholder1111111111111111111111111111111111111111111u', 5, 1),
    ('FAC-1002', '$2y$10$placeholder1111111111111111111111111111111111111111111u', 5, 1),
    ('FAC-1003', '$2y$10$placeholder1111111111111111111111111111111111111111111u', 5, 1),
    ('FAC-1004', '$2y$10$placeholder1111111111111111111111111111111111111111111u', 5, 1),
    ('FAC-1005', '$2y$10$placeholder1111111111111111111111111111111111111111111u', 5, 1),
    ('FAC-1006', '$2y$10$placeholder1111111111111111111111111111111111111111111u', 5, 1),
    ('FAC-1007', '$2y$10$placeholder1111111111111111111111111111111111111111111u', 5, 1),
    ('FAC-1008', '$2y$10$placeholder1111111111111111111111111111111111111111111u', 5, 1);

-- Insert faculty linked to those users
INSERT IGNORE INTO `faculty`
    (`faculty_code`, `user_id`, `first_name`, `last_name`,
     `designation`, `type`, `max_units`, `email`, `specialization`, `status`)
SELECT
    t.faculty_code,
    u.user_id,
    t.first_name, t.last_name,
    t.designation, t.type, t.max_units,
    t.email, t.specialization, 'Active'
FROM (
    SELECT 'FAC-1001' AS faculty_code, 'Miguel'   AS first_name, 'Santos'   AS last_name,
           'Associate Professor' AS designation, 'Full-Time' AS type, 21 AS max_units,
           'm.santos@school.edu' AS email,
           'Database Systems, Web Development' AS specialization
    UNION ALL
    SELECT 'FAC-1002','Maria','Reyes','Professor','Full-Time',21,
           'm.reyes@school.edu','Software Engineering, OOP'
    UNION ALL
    SELECT 'FAC-1003','Jose','Cruz','Assistant Professor','Full-Time',21,
           'j.cruz@school.edu','Networking, System Administration'
    UNION ALL
    SELECT 'FAC-1004','Ana','Garcia','Associate Professor','Full-Time',21,
           'a.garcia@school.edu','Programming, Algorithms'
    UNION ALL
    SELECT 'FAC-1005','Patricia','Lim','Instructor','Full-Time',21,
           'p.lim@school.edu','Mathematics, Statistics'
    UNION ALL
    SELECT 'FAC-1006','Roberto','Tan','Instructor','Part-Time',12,
           'r.tan@school.edu','Computer Architecture, OS'
    UNION ALL
    SELECT 'FAC-1007','Carmela','Reyes','Assistant Professor','Full-Time',21,
           'c.reyes@school.edu','HCI, Systems Analysis'
    UNION ALL
    SELECT 'FAC-1008','Dionisio','Bautista','Instructor','Part-Time',12,
           'd.bautista@school.edu','Programming, Data Structures'
) AS t
JOIN users u ON u.username = t.faculty_code AND u.role_id = 5;
