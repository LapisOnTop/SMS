<?php
$conn = new mysqli('localhost', 'root', '', 'sms_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. enrollments
$conn->query("ALTER TABLE enrollments DROP FOREIGN KEY enrollments_ibfk_1");
$conn->query("ALTER TABLE enrollments ADD CONSTRAINT enrollments_ibfk_1 FOREIGN KEY (student_id) REFERENCES students(student_id) ON UPDATE CASCADE");

// 2. grades
$conn->query("ALTER TABLE grades DROP FOREIGN KEY grades_ibfk_1");
$conn->query("ALTER TABLE grades ADD CONSTRAINT grades_ibfk_1 FOREIGN KEY (student_id) REFERENCES students(student_id) ON UPDATE CASCADE");

// 3. payments
$conn->query("ALTER TABLE payments DROP FOREIGN KEY payments_ibfk_2");
$conn->query("ALTER TABLE payments ADD CONSTRAINT payments_ibfk_2 FOREIGN KEY (student_id) REFERENCES students(student_id) ON UPDATE CASCADE");

// 4. student_discounts
$conn->query("ALTER TABLE student_discounts DROP FOREIGN KEY fk_discount_student");
$conn->query("ALTER TABLE student_discounts ADD CONSTRAINT fk_discount_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON UPDATE CASCADE");

echo "Foreign keys updated to ON UPDATE CASCADE successfully!\n";
?>
