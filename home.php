<?php
session_start();
include 'config.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = '';
$pending_count = 0;
$approved_count = 0;
$history_count = 0;

// Fetch user's name using prepared statement
$stmt_user = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
if ($stmt_user) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($user = $result_user->fetch_assoc()) {
        $user_name = $user['name'];
    }
    $stmt_user->close();
} else {
    error_log("Failed to prepare user name statement: " . $conn->error);
}


// Fetch request counts using prepared statement
$count_stmt = $conn->prepare("SELECT status_id, COUNT(*) as count FROM scrap_req WHERE user_id = ? GROUP BY status_id");
if($count_stmt) {
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    while($row = $count_result->fetch_assoc()){
        if($row['status_id'] == 1) $pending_count = $row['count']; // Pending
        if($row['status_id'] == 2) $approved_count = $row['count']; // Approved
        // History includes both rejected (3) and picked (4)
        if($row['status_id'] == 3 || $row['status_id'] == 4) $history_count += $row['count'];
    }
    $count_stmt->close();
} else {
     error_log("Failed to prepare request count statement: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Scrapify</title>
    <link rel="stylesheet" href="styles/dashboard.css">
</head>
<body class="body-bg">

<div class="dashboard-container">
    <div class="sidebar">
        <div class="sidebar-header">
             <a href="home.php" class="logo-container">
                <img class="logo-img" src="assets/WhatsApp_Image_2025-10-14_at_12.58.19_efe28561-removebg-preview.png" alt="Scrapify Logo">
                <h1 class="logo-text">Scrapify</h1>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a class="sidebar-link active" href="home.php">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            <a class="sidebar-link" href="sell_scrap.php">
                 <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01M12 6v-1m0 1V4m0 2.01V5M12 21a9 9 0 110-18 9 9 0 010 18z"></path></svg>
                Sell Scrap
            </a>
            <a class="sidebar-link" href="past_request.php">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                My Requests
            </a>
        </nav>
       
        <div class="sidebar-footer">
             <a class="logout-link" href="logout.php">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <header class="main-header">
            <h2 class="header-title">Dashboard</h2>
             <div class="user-info">
                 <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                 <img class="user-avatar" src="https://placehold.co/100x100/E2E8F0/4A5568?text=<?php echo htmlspecialchars(strtoupper(substr($user_name, 0, 1))); ?>" alt="User avatar">
            </div>
        </header>
        <main class="main-area">
            <div class="welcome-banner">
                <h3 class="welcome-title">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h3>
                <p class="welcome-subtitle">Here's a summary of your scrap selling activity.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card pending">
                     <div>
                        <p class="stat-title">Pending</p>
                        <p class="stat-number"><?php echo $pending_count; ?></p>
                     </div>
                     <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>

                <div class="stat-card approved">
                    <div>
                        <p class="stat-title">Approved</p>
                        <p class="stat-number"><?php echo $approved_count; ?></p>
                    </div>
                    <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>

                <div class="stat-card history">
                     <div>
                        <p class="stat-title">History</p>
                        <p class="stat-number"><?php echo $history_count; ?></p>
                     </div>
                      <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </div>
            </div>

            <div class="cta-card">
                <h3 class="cta-title">Ready to Sell More Scrap?</h3>
                <p class="cta-text">It's quick and easy. Get the best rates for your scrap today!</p>
                <a href="sell_scrap.php" class="cta-button">
                     <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01M12 6v-1m0 1V4m0 2.01V5M12 21a9 9 0 110-18 9 9 0 010 18z"></path></svg>
                    SELL SCRAP NOW
                </a>
            </div>
        </main>
    </div>
</div>

</body>
</html>