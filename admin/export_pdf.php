<?php
// admin/export_pdf.php
session_start();
require_once '../config/db.php';

// Protect route: admin login required
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$type = isset($_GET['type']) ? $_GET['type'] : 'attendance';

// Include FPDF library (download fpdf.php into admin/ or root if using server-side generation)
// If FPDF is not installed, fallback to browser print view below:
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Official Report - Kibaha Secondary School</title>
    <style>
        body { font-family: 'Times New Roman', serif; padding: 20px; color: #000; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18pt; text-transform: uppercase; }
        .header h3 { margin: 5px 0 0 0; font-size: 12pt; font-weight: normal; }
        .report-title { text-align: center; margin-bottom: 20px; text-transform: uppercase; text-decoration: underline; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 10pt; }
        th { background-color: #f2f2f2; }
        .footer { margin-top: 40px; display: flex; justify-content: space-between; font-size: 10pt; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print();">

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print();" style="padding: 10px 20px; font-weight: bold; cursor: pointer;">Print / Save as PDF</button>
        <a href="dashboard.php" style="margin-left: 15px;">Back to Dashboard</a>
    </div>

    <div class="header">
        <h1>KIBAHA SECONDARY SCHOOL</h1>
        <h3>OFFICIAL PARENT-TEACHER MEETING REPORT</h3>
        <p style="margin: 5px 0 0 0; font-size: 9pt;">Date Generated: <?= date('d M Y, h:i A') ?></p>
    </div>

    <?php if ($type === 'attendance'): ?>
        <?php
        $stmt = $pdo->query("
            SELECT p.parent_name, p.phone_number, p.attended_at, s.full_name AS student_name, s.admission_no 
            FROM parents_attendence p
            JOIN students_list s ON p.student_id = s.id
            ORDER BY p.attended_at DESC
        ");
        $records = $stmt->fetchAll();
        ?>
        <div class="report-title">Parent Attendance Register</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">S/N</th>
                    <th>Parent / Guardian Name</th>
                    <th>Phone Number</th>
                    <th>Student Name</th>
                    <th>Admission No</th>
                    <th>Time Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($records) > 0): ?>
                    <?php foreach ($records as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($row['parent_name']) ?></td>
                            <td><?= htmlspecialchars($row['phone_number']) ?></td>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['admission_no']) ?></td>
                            <td><?= date('d M Y, h:i A', strtotime($row['attended_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center;">No parent attendance recorded.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php else: ?>
        <?php
        $stmt = $pdo->query("
            SELECT s.full_name, s.admission_no, COUNT(p.id) AS attendance_count
            FROM students_list s
            LEFT JOIN parents_attendence p ON s.id = p.student_id
            GROUP BY s.id
            ORDER BY s.full_name ASC
        ");
        $students = $stmt->fetchAll();
        ?>
        <div class="report-title">Student Visitation Status Report</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">S/N</th>
                    <th>Admission No</th>
                    <th>Student Name</th>
                    <th>Visitation Status</th>
                    <th>Total Visits</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $index => $row): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($row['admission_no']) ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= $row['attendance_count'] > 0 ? 'VISITED' : 'NOT VISITED' ?></td>
                        <td><?= $row['attendance_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="footer">
        <div>Class Teacher Signature: _______________________</div>
        <div>Headmaster Stamp: _______________________</div>
    </div>

</body>
</html>