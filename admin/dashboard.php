<?php
// admin/dashboard.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Counts and Data Queries
$total_students = $pdo->query("SELECT COUNT(*) FROM students_list")->fetchColumn();
$parents_list = $pdo->query("
    SELECT p.parent_name, p.phone_number, p.attended_at, s.full_name AS student_name, s.admission_no 
    FROM parents_attendence p JOIN students_list s ON p.student_id = s.id ORDER BY p.attended_at DESC
")->fetchAll();

$students_list = $pdo->query("
    SELECT s.id, s.full_name, s.admission_no, COUNT(p.id) AS attendance_count
    FROM students_list s LEFT JOIN parents_attendence p ON s.id = p.student_id
    GROUP BY s.id ORDER BY s.full_name ASC
")->fetchAll();

$visited_students_count = 0;
foreach ($students_list as $st) { if ($st['attendance_count'] > 0) $visited_students_count++; }
$pending_students_count = $total_students - $visited_students_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kibaha Secondary School</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
</head>
<body>

    <header class="gov-header">
        <h1>KIBAHA SECONDARY SCHOOL</h1>
        <p>PARENT MEETING MANAGEMENT SYSTEM - ADMIN PORTAL</p>
    </header>

    <div class="container">
        <!-- Top Navigation -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>Welcome, <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong></div>
            <a href="logout.php" style="color: var(--danger); font-weight: 700; text-decoration: none;">Logout</a>
        </div>

        <!-- Alert Notifications -->
        <?php if (isset($_GET['msg'])): ?>
            <div style="background-color: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: bold;">
                ✓ <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_students ?></div>
                <div class="stat-label">Total Class Students</div>
            </div>
            <div class="stat-card" style="border-top-color: var(--success);">
                <div class="stat-number" style="color: var(--success);"><?= $visited_students_count ?></div>
                <div class="stat-label">Students Visited</div>
            </div>
            <div class="stat-card" style="border-top-color: var(--danger);">
                <div class="stat-number" style="color: var(--danger);"><?= $pending_students_count ?></div>
                <div class="stat-label">Not Visited</div>
            </div>
        </div>

        <!-- Roster Management Tools -->
        <div class="card">
            <h3 style="color: var(--gov-navy); margin-bottom: 15px;">Roster Management</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                
                <!-- CSV/Excel Upload -->
                <form action="student_actions.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_csv">
                    <div class="form-group">
                        <label>Upload Student List (CSV File):</label>
                        <input type="file" name="student_file" accept=".csv, .xlsx, .xls" required>
                        <small style="color: var(--text-muted);">Format: Admission No, Student Name</small>
                    </div>
                    <button type="submit" class="btn btn-success">Upload & Update Roster</button>
                </form>

                <!-- Manual Addition -->
                <div>
                    <div class="form-group">
                        <label>Manual Student Entry:</label>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">Add student individually with instant duplicate checking.</p>
                    </div>
                    <button type="button" class="btn btn-warning" onclick="openManualModal()">+ Add New Student</button>
                </div>

            </div>
        </div>

        <!-- Reports & Exports -->
        <div style="margin-bottom: 20px; display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap;">
            <button onclick="downloadPDF('attendanceTable', 'Parent_Attendance_Report')" class="btn" style="width: auto; background: var(--gov-blue);">Download Attendance PDF</button>
            <button onclick="downloadPDF('statusTable', 'Student_Visitation_Report')" class="btn" style="width: auto; background: var(--gov-navy);">Download Status PDF</button>
        </div>

        <!-- Tables -->
        <div class="card">
            <h3 style="color: var(--gov-navy); margin-bottom: 10px;">Attended Parents List</h3>
            <div class="table-responsive">
                <table class="data-table" id="attendanceTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Parent Name</th>
                            <th>Phone Number</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Time Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($parents_list) > 0): ?>
                            <?php foreach ($parents_list as $index => $row): ?>
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
                            <tr><td colspan="6" style="text-align: center;">No parent attendance recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h3 style="color: var(--gov-navy); margin-bottom: 10px;">Student Visitation Status</h3>
            <div class="table-responsive">
                <table class="data-table" id="statusTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Admission No</th>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th>Visits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students_list as $index => $student): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($student['admission_no']) ?></td>
                                <td><?= htmlspecialchars($student['full_name']) ?></td>
                                <td>
                                    <span class="badge <?= $student['attendance_count'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $student['attendance_count'] > 0 ? 'VISITED' : 'NOT VISITED' ?>
                                    </span>
                                </td>
                                <td><?= $student['attendance_count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Manual Student Modal -->
    <div id="manualModal" class="modal">
        <div class="modal-content">
            <h3 style="color: var(--gov-navy); margin-bottom: 15px;">Add Student Manually</h3>
            <form id="manualForm" action="student_actions.php" method="POST">
                <input type="hidden" name="action" value="add_manual_student">
                <input type="hidden" id="confirm_overwrite" name="confirm_overwrite" value="0">

                <div class="form-group">
                    <label>Admission Number:</label>
                    <input type="text" id="manual_adm" name="admission_no" placeholder="e.g., KSS/2026/010" required>
                </div>

                <div class="form-group">
                    <label>Student Full Name:</label>
                    <input type="text" id="manual_name" name="full_name" placeholder="e.g., Jane Doe" required>
                </div>

                <div id="duplicate_warning" style="display:none; background: #fff3cd; color: #856404; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.85rem;">
                    <strong>Warning:</strong> Admission No already exists for <span id="existing_student_name"></span>. Submitting will replace the record.
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn" style="background: #64748b;" onclick="closeManualModal()">Cancel</button>
                    <button type="submit" id="save_student_btn" class="btn btn-success">Save Student</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/pdf_export.js"></script>
    <script>
        function openManualModal() {
            document.getElementById('manualModal').style.display = 'flex';
        }
        function closeManualModal() {
            document.getElementById('manualModal').style.display = 'none';
            document.getElementById('duplicate_warning').style.display = 'none';
        }

        // Live Duplicate Check for Manual Form
        const admInput = document.getElementById('manual_adm');
        admInput.addEventListener('blur', function() {
            const adm = this.value.trim();
            if (adm.length > 0) {
                fetch(`student_actions.php?action=check_duplicate&admission_no=${encodeURIComponent(adm)}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.exists) {
                            document.getElementById('existing_student_name').innerText = data.existing_name;
                            document.getElementById('duplicate_warning').style.display = 'block';
                            document.getElementById('confirm_overwrite').value = '1';
                        } else {
                            document.getElementById('duplicate_warning').style.display = 'none';
                            document.getElementById('confirm_overwrite').value = '0';
                        }
                    });
            }
        });
    </script>
</body>
</html>