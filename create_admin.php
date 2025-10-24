<?php
// Use this file ONCE to generate a secure password hash for your admin user.
// 1. Run this file in your browser.
// 2. Copy the resulting hash.
// 3. Paste it into the 'password' column for the 'admin' user in your 'admins' table in phpMyAdmin.

$plainPassword = 'admin'; // The password you want to use
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

echo "<h3>Copy this hash into your database for the admin user:</h3>";
echo "<p style='font-family: monospace; background: #f0f0f0; padding: 10px; border-radius: 5px;'>" . $hashedPassword . "</p>";
?>
