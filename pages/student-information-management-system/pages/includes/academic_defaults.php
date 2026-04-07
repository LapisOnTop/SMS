<?php

declare(strict_types=1);

function academic_years(): array
{
    return ['2026-2025', '2025-2024', '2024-2023'];
}

function academic_semesters(): array
{
    return ['1st Semester', '2nd Semester'];
}

function default_course_list(): array
{
    return [
        ['code' => 'SA 101', 'name' => 'System Administration And Maintenance', 'units' => 3, 'grade' => 0],
        ['code' => 'ITSP2B', 'name' => 'Network Implementation And Support II', 'units' => 3, 'grade' => 0],
        ['code' => 'BPM101', 'name' => 'Business Process Management In IT', 'units' => 3, 'grade' => 0],
        ['code' => 'TEC101', 'name' => 'Technopreneurship', 'units' => 3, 'grade' => 0],
        ['code' => 'IAS102', 'name' => 'Information Assurance And Security 2', 'units' => 3, 'grade' => 0],
        ['code' => 'SP101', 'name' => 'Social and Professional Issues', 'units' => 3, 'grade' => 0],
    ];
}

function build_default_academic_records(): array
{
    $records = [];
    foreach (academic_years() as $year) {
        $records[$year] = [];
        foreach (academic_semesters() as $sem) {
            $records[$year][$sem] = default_course_list();
        }
    }
    return $records;
}
