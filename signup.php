<?php
session_start();
include 'config.php';

if(isset($_SESSION['user_id'])){
    header("Location: home.php");
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['user_address']);
    $password = trim($_POST['password']);

    if(empty($name)) $errors[] = "Name is required.";
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email is required.";
    if(empty($phone)) $errors[] = "Phone number is required.";
    if(empty($address)) $errors[] = "Address is required.";
    if(empty($password) || strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";

    if(empty($errors)){
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, user_address, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $address, $hashed_password);
        
        if($stmt->execute()){
            $_SESSION['user_id'] = $stmt->insert_id;
            header("Location: home.php");
            exit();
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Scrapify</title>
    <link rel="stylesheet" href="styles/auth.css">
</head>
<body class="auth-body">
    <div class="auth-container signup-container">
        <div class="auth-header">
            <h1 class="auth-title">Scrapify</h1>
            <p class="auth-subtitle">Create your account to start selling scrap.</p>
        </div>
        
        <?php if(!empty($errors)): ?>
        <div class="auth-alert">
            <?php foreach($errors as $error): ?>
            <span><?php echo $error; ?></span><br>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <form class="auth-form" action="signup.php" method="POST">
            <div class="input-group-signup">
                <input name="name" type="text" required class="auth-input" placeholder="Full Name">
                <input name="email" type="email" required class="auth-input" placeholder="Email address">
                <input name="phone" type="tel" required class="auth-input" placeholder="Phone Number">
                <textarea name="user_address" required class="auth-textarea" placeholder="Your Address"></textarea>
                <input name="password" type="password" required class="auth-input" placeholder="Password">
            </div>
            <div>
                <button type="submit" class="auth-button">Create Account</button>
            </div>
        </form>

        <p class="auth-footer-link">
            Already have an account?
            <a href="login.php">Sign in</a>
        </p>
    </div>
</body>
</html>
