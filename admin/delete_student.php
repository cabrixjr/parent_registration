<?php
// admin/delete_student.php
session_start();
require_once '../config/db.php';

// Verify admin session exists
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
        $stmt->execute([':id' => $student_id]);

        $_SESSION['flash_success'] = "Student record deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Failed to delete record: " . $e->getMessage();
    }
}

header("Location: dashboard.php");
exit();
?>
