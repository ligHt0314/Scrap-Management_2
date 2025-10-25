<?php
session_start();
include 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                header("Location: home.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Email not registered.";
        }
        $stmt->close();
    } else {
        $error = "Please enter both email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Scrapify</title>
    <link rel="stylesheet" href="styles/auth.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-header">
            <h1 class="auth-title">Scrapify</h1>
            <p class="auth-subtitle">Welcome back! Please sign in.</p>
        </div>
        
        <?php if(!empty($error)): ?>
        <div class="auth-alert">
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>
        
        <form class="auth-form" action="login.php" method="POST">
            <div class="input-group">
                <input id="email-address" name="email" type="email" required class="input-top" placeholder="Email address">
                <input id="password" name="password" type="password" required class="input-bottom" placeholder="Password">
            </div>

            <div>
                <button type="submit" class="auth-button">Sign in</button>
            </div>
        </form>

        <p class="auth-footer-link">
            Don't have an account?
            <a href="signup.php">Sign up</a>
        </p>
    </div>
</body>
</html>