<?php
session_start();
include 'config.php';

// Add the admin session check for security
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['req_id'])) {
    $req_id = $_POST['req_id'];

    // Check if the request exists and is in the 'approved' state (status_id = 2)
    $checkQuery = "SELECT req_id FROM scrap_req WHERE req_id = ? AND status_id = 2";
    $stmtCheck = $conn->prepare($checkQuery);
    $stmtCheck->bind_param("i", $req_id);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();

    if ($result->num_rows > 0) {
        // Update status to "Picked" (status_id = 4) and set the picked_date
        $query = "UPDATE scrap_req SET status_id = 4, picked_date = NOW() WHERE req_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $req_id);

        if ($stmt->execute()) {
            // **FIXED**: Redirect back to the admin dashboard on the 'approved' tab
            header("Location: admin_dashboard.php?tab=approved&update=success");
            exit();
        } else {
            // Error handling: Redirect with an error message
            header("Location: admin_dashboard.php?tab=approved&update=error");
            exit();
        }
        $stmt->close();
    } else {
        // Request not found or not in 'approved' state
        header("Location: admin_dashboard.php?tab=approved&update=notfound");
        exit();
    }
    $stmtCheck->close();
} else {
    // If accessed without POST data, just go back to the dashboard
    header("Location: admin_dashboard.php");
    exit();
}
?>