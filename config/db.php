<?php
// config/db.php - Supabase IPv4 Shared Pooler Configuration

// Extract your project reference ID from your old host:
// e.g., if old host was db.hqbajbpjedddzvxtglkf.supabase.co, your ref is hqbajbpjedddzvxtglkf
$project_ref = 'hqbajbpjedddzvxtglkf'; 

// Use the IPv4 shared pooler domain (AWS US-East regional pooler)
$host     = 'aws-0-us-east-1.pooler.supabase.com'; 
$port     = '5432'; // Port 5432 for Session Pooler mode
$dbname   = 'postgres';

// Shared Pooler requires the user format: postgres.[project-ref]
$user     = 'postgres.' . $project_ref; 
$password = 'cabrixjr2020@'; // Replace with your real Supabase password

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
