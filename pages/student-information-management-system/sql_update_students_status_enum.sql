-- Extend students.status enum to support SIM tracking statuses.
-- Run on sms_db in phpMyAdmin → SQL tab.

ALTER TABLE `students`
  MODIFY `status` ENUM('Applicant','Active','Inactive','On_Leave','Dropped','Graduated','Irregular')
  NOT NULL DEFAULT 'Applicant';

