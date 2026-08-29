<?php
// config/db.php

$host     = 'db.hqbajbpjedddzvxtglkf.supabase.co';
$port     = '5432';
$dbname   = 'postgres';
$user     = 'postgres';
$password = 'YOUR_ACTUAL_SUPABASE_PASSWORD';

try {
    // Force IPv4 lookup using gethostbyname
    $ipv4_host = gethostbyname($host);
    $dsn = "pgsql:host=$ipv4_host;port=$port;dbname=$dbname;user=$user;password=$password";
    
    $pdo = new PDO($dsn, null, null, [
        PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES         => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
