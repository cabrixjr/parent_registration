<?php
// config/db.php - Supabase PostgreSQL Connection

$host     = 'db.hqbajbpjedddzvxtglkf.supabase.co';
$port     = '5432';
$dbname   = 'postgres';
$user     = 'postgres';
$password = 'cabrixjr2020@'; // Replace this with your real Supabase database password

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";
    
    $pdo = new PDO($dsn, null, null, [
        PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES         => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
