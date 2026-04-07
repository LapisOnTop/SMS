<?php

declare(strict_types=1);

class StudentRepository
{
    private ?array $studentColumns = null;

    public function __construct(private PDO $pdo)
    {
    }

    private function ensureStudentMediaColumns(): void
    {
        // Best-effort schema upgrade so SIM can store profile media.
        // Safe to run repeatedly.
        try {
            $this->pdo->exec("ALTER TABLE students ADD COLUMN photo LONGBLOB NULL");
        } catch (Throwable $e) {
            // ignore (already exists or insufficient privileges)
        }
        try {
            $this->pdo->exec("ALTER TABLE students ADD COLUMN signature LONGBLOB NULL");
        } catch (Throwable $e) {
            // ignore
        }
        try {
            $this->pdo->exec("ALTER TABLE students ADD COLUMN contact_number VARCHAR(60) NULL");
        } catch (Throwable $e) {
            // ignore
        }
        // Reset cached column map so subsequent calls see newly-added columns.
        $this->studentColumns = null;
    }

    private function hasStudentColumn(string $column): bool
    {
        if (!is_array($this->studentColumns)) {
            $stmt = $this->pdo->prepare(
                'SELECT column_name
                 FROM information_schema.columns
                 WHERE table_schema = DATABASE() AND table_name = :table'
            );
            $stmt->execute(['table' => 'students']);
            $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $map = [];
            foreach ($cols as $c) {
                $map[(string) $c] = true;
            }
            $this->studentColumns = $map;
        }
        return !empty($this->studentColumns[$column]);
    }

    public function count(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS c FROM students');
        return (int) $stmt->fetchColumn();
    }

    public function getEnrollmentStatusSummary(): array
    {
        $stmt = $this->pdo->query(
            "SELECT COALESCE(s.status, 'Active') AS status_label, COUNT(DISTINCT s.student_id) AS c
             FROM students s
             GROUP BY status_label"
        );
        $byStatus = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $label = $row['status_label'] === 'Enrolled' ? 'Active' : $row['status_label'];
            if (!isset($byStatus[$label])) {
                $byStatus[$label] = 0;
            }
            $byStatus[$label] += (int) $row['c'];
        }
        $total = array_sum($byStatus);
        $active = $byStatus['Active'] ?? 0;
        $inactive = max(0, $total - $active);

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'byStatus' => $byStatus,
        ];
    }

    public function getAll(): array
    {
        $photoSel = $this->hasStudentColumn('photo') ? 's.photo' : 'NULL AS photo';
        $sigSel = $this->hasStudentColumn('signature') ? 's.signature' : 'NULL AS signature';
        $sql = "
            SELECT 
                s.student_id AS id, 
                s.student_number,
                $photoSel,
                $sigSel,
                s.status,
                api.first_name,
                api.last_name,
                api.birthdate AS birthdate,
                ac.contact_number AS admission_contact,
                ac.email,
                aa.region,
                aa.city_municipality,
                aa.barangay,
                c.course_name AS program_name,
                sel.year_level,
                e.status AS enrollment_status,
                s.created_at AS admission_date
            FROM students s
            LEFT JOIN enrollments e ON e.student_id = s.student_id
            LEFT JOIN applications app ON app.application_id = s.application_id
            LEFT JOIN selection sel ON sel.selection_id = app.selection_id
            LEFT JOIN courses c ON c.course_id = sel.course_id
            LEFT JOIN (
                SELECT
                    application_id,
                    first_name,
                    last_name,
                    birthdate,
                    sex AS gender
                FROM applicant_personal_info
            ) api ON api.application_id = s.application_id
            LEFT JOIN (
                SELECT
                    application_id,
                    email_address AS email,
                    contact_number
                FROM applicant_contact
            ) ac ON ac.application_id = s.application_id
            LEFT JOIN (
                SELECT
                    application_id,
                    region,
                    city AS city_municipality,
                    barangay
                FROM applicant_address
            ) aa ON aa.application_id = s.application_id
            ORDER BY s.student_id DESC
        ";
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $unique = [];
        foreach ($rows as $r) {
            if (!isset($unique[$r['id']])) {
                $unique[$r['id']] = $this->rowToStudent($r);
            }
        }
        return array_values($unique);
    }

    public function getById(string $id): ?array
    {
        $photoSel = $this->hasStudentColumn('photo') ? 's.photo' : 'NULL AS photo';
        $sigSel = $this->hasStudentColumn('signature') ? 's.signature' : 'NULL AS signature';
        $sql = "
            SELECT 
                s.student_id AS id, 
                s.student_number,
                $photoSel,
                $sigSel,
                s.status,
                api.first_name,
                api.last_name,
                api.birthdate AS birthdate,
                ac.contact_number AS admission_contact,
                ac.email,
                aa.region,
                aa.city_municipality,
                aa.barangay,
                e.status AS enrollment_status,
                c.course_name AS program_name,
                sel.year_level,
                s.created_at AS admission_date
            FROM students s
            LEFT JOIN enrollments e ON e.student_id = s.student_id
            LEFT JOIN applications app ON app.application_id = s.application_id
            LEFT JOIN selection sel ON sel.selection_id = app.selection_id
            LEFT JOIN courses c ON c.course_id = sel.course_id
            LEFT JOIN (
                SELECT
                    application_id,
                    first_name,
                    last_name,
                    birthdate,
                    sex AS gender
                FROM applicant_personal_info
            ) api ON api.application_id = s.application_id
            LEFT JOIN (
                SELECT
                    application_id,
                    email_address AS email,
                    contact_number
                FROM applicant_contact
            ) ac ON ac.application_id = s.application_id
            LEFT JOIN (
                SELECT
                    application_id,
                    region,
                    city AS city_municipality,
                    barangay
                FROM applicant_address
            ) aa ON aa.application_id = s.application_id
            WHERE s.student_id = :id
            ORDER BY e.enrollment_id DESC LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->rowToStudent($row) : null;
    }

    public function insert(array $student): array
    {
        $this->ensureStudentMediaColumns();
        $applicationId = isset($student['application_id']) ? (int) $student['application_id'] : 0;
        if ($applicationId <= 0) {
            throw new RuntimeException('application_id required');
        }

        // If student already exists for this application, return it.
        $stmtExisting = $this->pdo->prepare('SELECT student_id FROM students WHERE application_id = :app LIMIT 1');
        $stmtExisting->execute(['app' => $applicationId]);
        $existingId = $stmtExisting->fetchColumn();
        if ($existingId) {
            $found = $this->getById((string) $existingId);
            if ($found) {
                return $found;
            }
        }

        // Create the student row first (AUTO_INCREMENT), then build student_number from generated id.
        $yy = (new DateTimeImmutable('now'))->format('y');

        $status = 'Applicant';
        $this->pdo->beginTransaction();
        try {
            // Create placeholder student row to reserve AUTO_INCREMENT id.
            $stmtCreateStudent = $this->pdo->prepare(
                'INSERT INTO students (student_number, user_id, status, application_id)
                 VALUES (NULL, NULL, :status, :app)'
            );
            $stmtCreateStudent->execute([
                'status' => $status,
                'app' => $applicationId,
            ]);
            $studentId = (int) $this->pdo->lastInsertId();
            if ($studentId <= 0) {
                throw new RuntimeException('Could not allocate student_id');
            }

            // Desired format: YY + 6-digit sequence, e.g. 26 + 000007 = 26000007
            $studentNumber = $yy . str_pad((string) $studentId, 6, '0', STR_PAD_LEFT);

            // Create or reuse a user account for the student so tracking/auth can map via student_number.
            $roleId = 1; // roles.role_id = 1 in seed sql => Student
            $stmtUser = $this->pdo->prepare('SELECT user_id FROM users WHERE username = :u LIMIT 1');
            $stmtUser->execute(['u' => $studentNumber]);
            $userId = (int) ($stmtUser->fetchColumn() ?: 0);

            if ($userId <= 0) {
                $stmtCreateUser = $this->pdo->prepare(
                    'INSERT INTO users (username, password_hash, role_id, is_active)
                     VALUES (:u, :p, :rid, 1)'
                );
                // Using legacy seed style: password_hash may be plain text.
                $stmtCreateUser->execute([
                    'u' => $studentNumber,
                    'p' => $studentNumber,
                    'rid' => $roleId,
                ]);
                $userId = (int) $this->pdo->lastInsertId();
                if ($userId <= 0) {
                    throw new RuntimeException('Could not create user');
                }
            }

            // Finalize student row.
            $stmtFinalize = $this->pdo->prepare(
                'UPDATE students SET student_number = :num, user_id = :uid WHERE student_id = :sid'
            );
            $stmtFinalize->execute([
                'num' => $studentNumber,
                'uid' => $userId,
                'sid' => $studentId,
            ]);

            // Save photo/signature if provided as data URLs.
            $photoUrl = (string) ($student['photo_data'] ?? $student['photoUrl'] ?? '');
            $signatureUrl = (string) ($student['signature_data'] ?? $student['signatureUrl'] ?? '');

            $photoBlob = null;
            if ($photoUrl !== '' && str_contains($photoUrl, 'base64,')) {
                $photoBlob = base64_decode(substr($photoUrl, strpos($photoUrl, 'base64,') + 7)) ?: null;
            }
            $sigBlob = null;
            if ($signatureUrl !== '' && str_contains($signatureUrl, 'base64,')) {
                $sigBlob = base64_decode(substr($signatureUrl, strpos($signatureUrl, 'base64,') + 7)) ?: null;
            }

            if (($photoBlob !== null || $sigBlob !== null) && $this->hasStudentColumn('photo') && $this->hasStudentColumn('signature')) {
                $stmtMedia = $this->pdo->prepare('UPDATE students SET photo = :p, signature = :s WHERE student_id = :id');
                $stmtMedia->bindValue('p', $photoBlob, $photoBlob === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);
                $stmtMedia->bindValue('s', $sigBlob, $sigBlob === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);
                $stmtMedia->bindValue('id', $studentId, PDO::PARAM_INT);
                $stmtMedia->execute();
            }

            // Ensure at least one enrollment row exists (for SIM status/reporting).
            $stmtEnroll = $this->pdo->prepare('SELECT enrollment_id FROM enrollments WHERE student_id = :sid LIMIT 1');
            $stmtEnroll->execute(['sid' => $studentId]);
            $hasEnroll = $stmtEnroll->fetchColumn();
            if (!$hasEnroll) {
                $stmtCreateEnroll = $this->pdo->prepare(
                    "INSERT INTO enrollments (student_id, term_id, status, created_at)
                     VALUES (:sid, 1, 'Enrolled', NOW())"
                );
                $stmtCreateEnroll->execute(['sid' => $studentId]);
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        $saved = $this->getById((string) $studentId);
        if (!$saved) {
            throw new RuntimeException('Insert failed');
        }
        return $saved;
    }

    public function update(string $id, array $fields): ?array
    {
        $this->ensureStudentMediaColumns();
        $existing = $this->getById($id);
        if (!$existing) {
            return null;
        }

        $updateStudents = [];
        $paramsStudents = ['id' => $id];
        
        if (isset($fields['photoUrl'])) {
            $photoParts = explode(',', $fields['photoUrl']);
            $paramsStudents['photo'] = count($photoParts) > 1 ? base64_decode($photoParts[1]) : null;
            $updateStudents[] = "photo = :photo";
        }
        if (isset($fields['signatureUrl'])) {
            $sigParts = explode(',', $fields['signatureUrl']);
            $paramsStudents['signature'] = count($sigParts) > 1 ? base64_decode($sigParts[1]) : null;
            $updateStudents[] = "signature = :signature";
        }
        if (isset($fields['contactNumber'])) {
            $paramsStudents['contact_number'] = $fields['contactNumber'];
            $updateStudents[] = "contact_number = :contact_number";
        }

        if (isset($fields['status'])) {
            $paramsStudents['status'] = (string) $fields['status'];
            $updateStudents[] = "status = :status";
        }
        
        if (!empty($updateStudents)) {
            $sql = "UPDATE students SET " . implode(", ", $updateStudents) . " WHERE student_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($paramsStudents);
        }

        $sqlExtract = "SELECT u.user_id, s.application_id FROM students s LEFT JOIN users u ON s.user_id = u.user_id WHERE s.student_id = :id LIMIT 1";
        $stmtMap = $this->pdo->prepare($sqlExtract);
        $stmtMap->execute(['id' => $id]);
        $map = $stmtMap->fetch(PDO::FETCH_ASSOC);
        if ($map) {
            if (isset($fields['email']) && !empty($map['application_id'])) {
                // users table does not have an email column in this project schema.
                // Keep the source of truth in applicant_contact.
                $uSql = "UPDATE applicant_contact SET email_address = :email WHERE application_id = :aid";
                $uStmt = $this->pdo->prepare($uSql);
                $uStmt->execute(['email' => $fields['email'], 'aid' => $map['application_id']]);
            }
            if (isset($fields['contactNumber']) && !empty($map['application_id'])) {
                $uSql = "UPDATE applicant_contact SET contact_number = :c WHERE application_id = :aid";
                $uStmt = $this->pdo->prepare($uSql);
                $uStmt->execute(['c' => $fields['contactNumber'], 'aid' => $map['application_id']]);
            }
            if (isset($fields['address']) && !empty($map['application_id'])) {
                $parts = explode('|~|', $fields['address']);
                $barangay = $parts[0] ?? '';
                $city = $parts[1] ?? '';
                $region = $parts[2] ?? '';
                
                $uSql = "UPDATE applicant_address SET barangay = :b, city = :c, region = :r WHERE application_id = :aid";
                $uStmt = $this->pdo->prepare($uSql);
                $uStmt->execute(['b' => $barangay, 'c' => $city, 'r' => $region, 'aid' => $map['application_id']]);
            }
        }
        
        return $this->getById($id);
    }

    private function rowToStudent(array $row): array
    {
        $firstName = $row['first_name'] ?? '';
        $lastName = $row['last_name'] ?? '';
        
        $addressParts = [
            $row['barangay'] ?? '',
            $row['city_municipality'] ?? '',
            $row['region'] ?? '',
            '', 
            $row['gender'] ?? '' 
        ];
        
        $photoBase64 = null;
        if (!empty($row['photo'])) {
            $photoBase64 = 'data:image/webp;base64,' . base64_encode($row['photo']);
        }
        
        $signatureBase64 = null;
        if (!empty($row['signature'])) {
            $signatureBase64 = 'data:image/png;base64,' . base64_encode($row['signature']);
        }

        $status = 'Active';
        if (!empty($row['status'])) {
            $status = (string) $row['status'];
        } elseif (isset($row['enrollment_status'])) {
            $status = $row['enrollment_status'] === 'Enrolled' ? 'Active' : $row['enrollment_status'];
        }

        return [
            'id' => (string) $row['id'],
            'studentNumber' => isset($row['student_number']) ? (string) $row['student_number'] : null,
            'student_number' => isset($row['student_number']) ? (string) $row['student_number'] : null,
            'fullName' => trim("$firstName $lastName"),
            'full_name' => trim("$firstName $lastName"),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birthdate' => $row['birthdate'] ?? '',
            'contactNumber' => $row['student_contact'] ?? $row['admission_contact'] ?? '',
            'email' => $row['email'] ?? '',
            'address' => implode('|~|', $addressParts),
            'program' => $row['program_name'] ?? '',
            'program_name' => $row['program_name'] ?? '',
            'yearLevel' => $row['year_level'] ?? '',
            'year_level' => $row['year_level'] ?? '',
            'admissionDate' => $row['admission_date'] ? substr($row['admission_date'], 0, 10) : '',
            'admission_date' => $row['admission_date'] ? substr($row['admission_date'], 0, 10) : '',
            'photoUrl' => $photoBase64,
            'signatureUrl' => $signatureBase64,
            'idValidity' => null,
            'status' => $status,
            'achievements' => [],
            'academicRecords' => $this->mergeAcademicDefaults([]),
        ];
    }

    private function mergeAcademicDefaults(array $existing): array
    {
        $full = build_default_academic_records();
        foreach (academic_years() as $year) {
            foreach (academic_semesters() as $sem) {
                $entries = $existing[$year][$sem] ?? null;
                if (is_array($entries) && count($entries) > 0) {
                    $full[$year][$sem] = array_map(function ($entry) {
                        return [
                            'code' => $entry['code'] ?? '',
                            'name' => $entry['name'] ?? '',
                            'units' => (int) ($entry['units'] ?? 0),
                            'grade' => isset($entry['grade']) && $entry['grade'] !== ''
                                ? (float) $entry['grade']
                                : 0,
                        ];
                    }, $entries);
                }
            }
        }
        return $full;
    }
}
