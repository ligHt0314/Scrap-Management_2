<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = '';

$stmt_user = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
if($stmt_user) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $stmt_user->bind_result($user_name);
    $stmt_user->fetch();
    $stmt_user->close();
} else {
     error_log("Failed to prepare user name statement: " . $conn->error);
}

$query = "SELECT r.req_id, r.material, r.weight, r.image_path, r.pick_address, r.request_date, r.approved_date, r.picked_date, rs.status_name
          FROM scrap_req r
          JOIN req_status rs ON r.status_id = rs.status_id
          WHERE r.user_id = ?
          ORDER BY r.request_date DESC";
$stmt = $conn->prepare($query);
$result = null;
if($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    error_log("Failed to prepare requests statement: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Scrap Requests - Scrapify</title>
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
            <a class="sidebar-link" href="home.php">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            <a class="sidebar-link" href="sell_scrap.php">
                 <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01M12 6v-1m0 1V4m0 2.01V5M12 21a9 9 0 110-18 9 9 0 010 18z"></path></svg>
                Sell Scrap
            </a>
            <a class="sidebar-link active" href="past_request.php">
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
            <h2 class="header-title">My Scrap Requests</h2>
             <div class="user-info">
                 <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                 <img class="user-avatar" src="https://placehold.co/100x100/E2E8F0/4A5568?text=<?php echo htmlspecialchars(strtoupper(substr($user_name, 0, 1))); ?>" alt="User avatar">
            </div>
        </header>
        <main class="main-area">
            <?php if ($result === null): ?>
                 <p style="color: #dc2626;">Error loading requests. Please try again later.</p>
            <?php elseif ($result->num_rows === 0): ?>
                <div class="empty-state">
                     <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                     </svg>
                     <h3 class="empty-state-title">No requests yet</h3>
                    <p class="empty-state-text">You haven't made any scrap requests. Get started by selling some scrap!</p>
                    <div style="margin-top: 1.5rem;">
                       <a href="sell_scrap.php" class="empty-state-button">
                           Sell Scrap Now
                       </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="requests-grid">
                    <?php while ($row = $result->fetch_assoc()):
                        $image_display_path = (!empty($row['image_path']) && file_exists($row['image_path']))
                            ? htmlspecialchars($row['image_path'])
                            : 'https://placehold.co/600x400/E2E8F0/CBD5E0?text=No+Image';
                        
                        $status_class = 'status-unknown';
                        $status_name_lower = strtolower($row['status_name']);
                        if ($status_name_lower == 'requested') $status_class = 'status-requested';
                        if ($status_name_lower == 'approved') $status_class = 'status-approved';
                        if ($status_name_lower == 'picked') $status_class = 'status-picked';
                        if ($status_name_lower == 'rejected') $status_class = 'status-rejected';
                    ?>
                    <div class="request-card">
                        <img src="<?php echo $image_display_path; ?>" alt="<?php echo htmlspecialchars($row['material']); ?> Scrap" class="request-card-img">
                        <div class="request-card-body">
                            <div class="request-card-header">
                                <h3 class="request-card-title">
                                    <?php echo htmlspecialchars($row['material']); ?>
                                    <span class="request-card-weight">(<?php echo htmlspecialchars($row['weight']); ?> kg)</span>
                                </h3>
                                <span class="request-card-status <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($row['status_name']); ?>
                                </span>
                            </div>
                            <p class="request-card-address" title="<?php echo htmlspecialchars($row['pick_address']); ?>">
                                <strong>Address:</strong> <?php echo htmlspecialchars($row['pick_address']); ?>
                            </p>
                            <div class="request-card-footer">
                                <p><strong>Requested:</strong> <?php echo date("M d, Y", strtotime($row['request_date'])); ?></p>
                                <p><strong>Approved:</strong> <?php echo !empty($row['approved_date']) ? date("M d, Y", strtotime($row['approved_date'])) : "<span>N/A</span>"; ?></p>
                                <p><strong>Picked:</strong> <?php echo !empty($row['picked_date']) ? date("M d, Y", strtotime($row['picked_date'])) : "<span>N/A</span>"; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
             <?php endif; ?>
        </main>
    </div>
</div>

</body>
</html>