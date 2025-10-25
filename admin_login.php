<?php
session_start();
include 'config.php';

if (isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT admin_id, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($admin_id, $db_password);
            $stmt->fetch();

            if ($password === $db_password) {
                $_SESSION["admin_logged_in"] = true;
                $_SESSION["admin_id"] = $admin_id;
                header("location: admin_dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No account found with that username.";
        }
        $stmt->close();
    } else {
        $error = "Please fill in both fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Scrapify</title>
    <link rel="stylesheet" href="styles/auth.css">
</head>
<body class="auth-body admin-body">
    <div class="auth-container">
        <div class="auth-header">
            <h1 class="auth-title">Scrapify</h1>
            <p class="auth-subtitle">Admin Panel</p>
        </div>
        
        <?php if(!empty($error)): ?>
        <div class="auth-alert">
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>
        
        <form class="auth-form" action="admin_login.php" method="POST">
            <div class="input-group">
                <input name="username" type="text" required class="input-top" placeholder="Username">
                <input name="password" type="password" required class="input-bottom" placeholder="Password">
            </div>
            <div>
                <button type="submit" class="auth-button">Sign in</button>
            </div>
        </form>
    </div>
</body>
</html>