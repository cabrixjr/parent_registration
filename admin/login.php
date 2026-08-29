<?php
// admin/login.php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = $user['full_name'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Kibaha Secondary School</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="gov-header">
        <h1>KIBAHA SECONDARY SCHOOL</h1>
        <p>ADMINISTRATION PORTAL</p>
    </header>

    <div class="container" style="max-width: 450px;">
        <div class="card">
            <h2 style="color: var(--gov-navy); margin-bottom: 20px; font-size: 1.2rem; text-align: center;">
                ADMINISTRATOR LOGIN
            </h2>

            <?php if (!empty($error)): ?>
                <div style="background-color: #fee2e2; color: #991b1b; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 0.9rem;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn">LOGIN TO DASHBOARD</button>
            </form>
        </div>
    </div>

</body>
</html>