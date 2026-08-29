<?php
// api/search_students.php
header('Content-Type: application/json');
require_once '../config/db.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

try {
    // PostgreSQL ILIKE for case-insensitive search and TRIM for clean character matching
    $stmt = $pdo->prepare("
        SELECT id, full_name, admission_no 
        FROM students_list 
        WHERE TRIM(full_name) ILIKE :query 
           OR TRIM(admission_no) ILIKE :query 
        ORDER BY full_name ASC 
        LIMIT 8
    ");
    
    $stmt->execute(['query' => '%' . $query . '%']);
    $students = $stmt->fetchAll();

    echo json_encode($students);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
