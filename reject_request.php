<?php
session_start();
include 'config.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['req_id'])) {
    $req_id = $_POST['req_id'];

    $query = "UPDATE scrap_req SET status_id = 3 WHERE req_id = ? AND status_id = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $req_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?status=rejected");
    } else {
        header("Location: admin_dashboard.php?status=error");
    }
    $stmt->close();
} else {
    header("Location: admin_dashboard.php");
}
exit();
?>
