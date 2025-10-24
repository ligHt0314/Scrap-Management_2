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

            if ($password === $db_password) { // Plain text password check as per DB
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen">
    <div class="w-full max-w-sm p-8 space-y-8 bg-white rounded-2xl shadow-lg">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-green-600">Scrapify</h1>
            <p class="mt-2 text-gray-600">Admin Panel</p>
        </div>
        
        <?php if(!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $error; ?></span>
        </div>
        <?php endif; ?>
        
        <form class="mt-8 space-y-6" action="admin_login.php" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <input id="username" name="username" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="Username">
                <input id="password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="Password">
            </div>
            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Sign in</button>
            </div>
        </form>
    </div>
</body>
</html>
