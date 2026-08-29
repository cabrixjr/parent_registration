<?php
// submit_registration.php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parent_name = trim($_POST['parent_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');

    // Basic validation
    if (empty($parent_name) || empty($phone_number) || empty($student_id)) {
        header('Location: index.php?error=missing_fields');
        exit;
    }

    try {
        // Verify student exists
        $checkStmt = $pdo->prepare("SELECT id FROM students_list WHERE id = :id");
        $checkStmt->execute(['id' => $student_id]);

        if (!$checkStmt->fetch()) {
            header('Location: index.php?error=invalid_student');
            exit;
        }

        // Insert attendance record
        $stmt = $pdo->prepare("INSERT INTO parents_attendence (student_id, parent_name, phone_number) VALUES (:student_id, :parent_name, :phone_number)");
        $stmt->execute([
            'student_id' => $student_id,
            'parent_name' => $parent_name,
            'phone_number' => $phone_number
        ]);

        header('Location: index.php?status=success');
        exit;

    } catch (PDOException $e) {
        header('Location: index.php?error=db_error');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>