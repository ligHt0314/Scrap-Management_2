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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen py-12">
    <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-2xl shadow-lg">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-green-600">Scrapify</h1>
            <p class="mt-2 text-gray-600">Create your account to start selling scrap.</p>
        </div>
        
        <?php if(!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <?php foreach($errors as $error): ?>
            <span class="block sm:inline"><?php echo $error; ?></span><br>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <form class="mt-8 space-y-6" action="signup.php" method="POST">
            <div class="rounded-md shadow-sm space-y-4">
                <input id="name" name="name" type="text" required class="appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="Full Name">
                <input id="email-address" name="email" type="email" required class="appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="Email address">
                <input id="phone" name="phone" type="tel" required class="appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="Phone Number">
                <textarea id="address" name="user_address" required class="appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="Your Address"></textarea>
                <input id="password" name="password" type="password" required class="appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="Password">
            </div>
            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Create Account</button>
            </div>
        </form>

        <p class="mt-2 text-center text-sm text-gray-600">
            Already have an account?
            <a href="login.php" class="font-medium text-green-600 hover:text-green-500">Sign in</a>
        </p>
    </div>
</body>
</html>
