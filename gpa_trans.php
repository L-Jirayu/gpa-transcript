<?php
/**
 * GPA Calculator CLI (4.00 scale, Thai style F–A + Status Pass/Fail)
 * Output: transcript.txt และ transcript.csv (CSV มีบรรทัด Total/GPA ท้ายตาราง)
 */

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "Run this script in CLI only.\n");
    exit(1);
}

$courses = interactiveInput();
if (empty($courses)) {
    fwrite(STDERR, "No courses provided. Bye.\n");
    exit(0);
}

list($report, $gpa, $csvLines) = buildReport($courses);
echo $report;

// save .txt
file_put_contents("transcript.txt", $report);
println("Saved transcript.txt");

// save .csv
$csvPath = "transcript.csv";
$fh = fopen($csvPath, "w");
fputcsv($fh, ["Course", "Credits", "Grade", "Points", "Status"]);
foreach ($csvLines as $row) {
    fputcsv($fh, $row);
}
fclose($fh);
println("Saved transcript.csv");

/* -------------------- Helpers -------------------- */

function interactiveInput(): array {
    println("=== GPA Calculator (Scale 4.00, F–A) ===");
    println("กด Enter ที่ชื่อวิชาเพื่อจบ");
    $rows = [];
    while (true) {
        $course  = trim(ask("Course name: "));
        if ($course === '') break;

        $credits = askFloat("Credits (e.g., 3): ", 0.0, 100.0);
        $grade   = trim(ask("Grade (F,D,D+,C,C+,B,B+,A) / point / percent): "));

        $rows[] = [
            'course'  => $course,
            'credits' => $credits,
            'grade'   => $grade,
        ];
    }
    return $rows;
}

function buildReport(array $courses): array {
    $now = date('Y-m-d H:i:s');
    $lines = [];
    $lines[] = "========== Transcript ==========";
    $lines[] = "Generated: {$now}";
    $lines[] = "Scale: 4.00";
    $lines[] = "--------------------------------";
    $lines[] = sprintf("%-25s %8s %10s %10s %10s", "Course", "Credits", "Grade", "Points", "Status");

    $csvLines = [];

    $totalCredits = 0.0;
    $totalWeighted = 0.0;

    foreach ($courses as $row) {
        $course  = $row['course'];
        $credits = (float)$row['credits'];
        $gradeRaw= $row['grade'];

        $point = normalizeGradeToPoint($gradeRaw);
        $status = ($point > 0.0) ? "Pass" : "Fail";

        $totalCredits  += $credits;
        $totalWeighted += ($point * $credits);

        $gradeDisp = normalizeDisplay($gradeRaw);
        $lines[] = sprintf(
            "%-25s %8.2f %10s %10.2f %10s",
            truncate($course, 25),
            $credits,
            $gradeDisp,
            $point,
            $status
        );

        $csvLines[] = [$course, round($credits,2), $gradeDisp, round($point,2), $status];
    }

    $lines[] = "--------------------------------";
    $gpa = ($totalCredits > 0) ? ($totalWeighted / $totalCredits) : 0.0;
    $lines[] = sprintf("Total Credits: %.2f", $totalCredits);
    $lines[] = sprintf("GPA (4.00): %.2f", $gpa);
    $lines[] = "================================\n";

    // ⭐ เพิ่มบรรทัดสรุปใน CSV (คอลัมน์แรกเป็น label, ค่าที่คอลัมน์ที่สอง)
    $csvLines[] = ["", "", "", "", ""]; // เว้นบรรทัดคั่น (optional)
    $csvLines[] = ["Total Credits:", round($totalCredits,2), "", "", ""];
    $csvLines[] = ["GPA (4.00):", round($gpa,2), "", "", ""];

    return [implode("\n", $lines), $gpa, $csvLines];
}

function normalizeGradeToPoint(string $gradeRaw): float {
    $g = strtoupper(trim($gradeRaw));

    if (is_numeric($g)) {
        $val = (float)$g;
        if ($val >= 0 && $val <= 100) {
            $letter = percentToLetter($val);
            return letterToPoint($letter);
        } else {
            return max(0.0, min($val, 4.0));
        }
    }

    return letterToPoint($g);
}

function normalizeDisplay(string $gradeRaw): string {
    $g = trim($gradeRaw);
    if (is_numeric($g)) {
        $val = (float)$g;
        if ($val >= 0 && $val <= 100) {
            return percentToLetter($val);
        } else {
            return (string)round(max(0.0, min($val, 4.0)), 2);
        }
    }
    return strtoupper($g);
}

function percentToLetter(float $p): string {
    if ($p >= 80) return 'A';
    if ($p >= 75) return 'B+';
    if ($p >= 70) return 'B';
    if ($p >= 65) return 'C+';
    if ($p >= 60) return 'C';
    if ($p >= 55) return 'D+';
    if ($p >= 50) return 'D';
    return 'F';
}

function letterToPoint(string $letter): float {
    $map = [
        'A'  => 4.00,
        'B+' => 3.50,
        'B'  => 3.00,
        'C+' => 2.50,
        'C'  => 2.00,
        'D+' => 1.50,
        'D'  => 1.00,
        'F'  => 0.00,
    ];
    return $map[$letter] ?? 0.0;
}

function ask(string $prompt): string {
    if (function_exists('readline')) {
        $res = readline($prompt);
        if ($res !== false) return $res;
    }
    echo $prompt;
    $res = fgets(STDIN);
    return $res === false ? '' : rtrim($res, "\r\n");
}

function askFloat(string $prompt, float $min, float $max): float {
    while (true) {
        $s = ask($prompt);
        if (is_numeric($s)) {
            $v = (float)$s;
            if ($v >= $min && $v <= $max) return $v;
        }
        println("Invalid number, please try again.");
    }
}

function println(string $s): void { echo $s . PHP_EOL; }
function truncate(string $s, int $n): string { return (strlen($s) > $n) ? substr($s, 0, $n-1).'…' : $s; }
