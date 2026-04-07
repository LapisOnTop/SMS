# Database: sms_db

All system modules now connect to a single database: `sms_db`

## Key Tables Used by This System

| Table              | Purpose                                      |
|--------------------|----------------------------------------------|
| `sections`         | Class sections (section_name, subject_id, teacher_id, room, day, start_time, end_time, capacity) |
| `section_subjects` | Links sections to multiple subjects + teacher + schedule |
| `subjects`         | All subjects (subject_code, subject_name, units, year_level, semester) |
| `subject_prerequisite` | Prerequisite chains between subjects       |
| `users`            | All system users (students, faculty, admin)  |
| `roles`            | Role definitions (Student, Faculty, Admin…)  |
| `terms`            | Academic terms (school_year, semester)       |
| `students`         | Student records linked to users              |
| `enrollments`      | Student term enrollments                     |
| `enrollment_details` | Links enrollments to section_subjects      |
| `courses`          | Course/program definitions (BSIT, etc.)      |
| `grades`           | Grade records                                |

Import `sms_db.sql` to set up this database.
