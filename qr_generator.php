<?php
// qr_generator.php

// Determine current server protocol and host dynamically
$protocol = isset($_SERVER['HTTPS']) &&$_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host =$_SERVER['HTTP_HOST'];
$currentDir = dirname($_SERVER['REQUEST_URI']);

// Build full path to registration index.php
$registrationUrl =$protocol . "://" . $host .$currentDir . "/index.php";
$qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($registrationUrl);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Code - Kibaha Secondary School</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header class="gov-header">
        <h1>KIBAHA SECONDARY SCHOOL</h1>
        <p>PARENT MEETING ATTENDANCE REGISTRATION</p>
    </header>

    <div class="container" style="max-width: 500px; text-align: center;">
        <div class="card">
            <h2 style="color: var(--gov-navy); margin-bottom: 10px;">WELCOME PARENTS / GUARDIANS</h2>
            <p style="color: var(--text-muted); margin-bottom: 20px;">Please scan the QR code below with your smartphone camera to record your attendance.</p>
            
            <div style="padding: 15px; background: #ffffff; display: inline-block; border: 2px dashed var(--gov-blue); border-radius: 8px;">
                <img src="<?= $qrApiUrl ?>" alt="Scan to Register" style="max-width: 100%; height: auto;">
            </div>

            <p style="margin-top: 20px; font-size: 0.85rem; color: var(--text-muted);">
                Direct URL: <a href="<?= $registrationUrl ?>" style="color: var(--gov-blue); font-weight: bold;"><?= $registrationUrl ?></a>
            </p>
        </div>
    </div>

</body>
</html>