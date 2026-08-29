<?php
// config/db.php - Supabase IPv4 Pooler Setup

// Replace with the EXACT Host copied from your Supabase Connect modal
$host     = 'aws-1-eu-west-1.pooler.supabase.com'; // e.g., aws-0-eu-west-1.pooler.supabase.com or aws-1-us-east-2...
$port     = '5432';                                 // Use port 6543 for Transaction Mode or 5432 for Session Mode
$dbname   = 'postgres';

// Format MUST be postgres.[your-project-ref]
$user     = 'postgres.hqbajbpjedddzvxtglkf'; 
$password = 'cabrixjr2020@';        // Replace with your real Supabase password

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
