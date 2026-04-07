-- Adds media columns used by SIM (profile photo + signature).
-- Run on sms_db using phpMyAdmin SQL tab.

ALTER TABLE `students`
  ADD COLUMN `photo` LONGBLOB NULL,
  ADD COLUMN `signature` LONGBLOB NULL;

