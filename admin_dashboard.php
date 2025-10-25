<?php
session_start();
include 'config.php';

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin_login.php");
    exit();
}

function fetch_requests($conn, $status_id) {
    $sql = "SELECT r.req_id, r.material, r.weight, r.pick_address, r.request_date, r.approved_date, r.picked_date, r.image_path,
                   u.name AS user_name, u.phone AS user_phone
            FROM scrap_req r
            JOIN users u ON r.user_id = u.user_id
            WHERE r.status_id = ?
            ORDER BY r.request_date DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed for status " . $status_id . ": (" . $conn->errno . ") " . $conn->error);
        return false;
    }
    $stmt->bind_param("i", $status_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

$pending_result = fetch_requests($conn, 1);
$approved_result = fetch_requests($conn, 2);
$picked_result = fetch_requests($conn, 4);
$rejected_result = fetch_requests($conn, 3);

if ($pending_result === false || $approved_result === false || $picked_result === false || $rejected_result === false) {
     $dashboard_error = "Error fetching some request data. Please try again later.";
     $pending_result = $pending_result ?: $conn->query("SELECT 1 FROM scrap_req WHERE 1=0");
     $approved_result = $approved_result ?: $conn->query("SELECT 1 FROM scrap_req WHERE 1=0");
     $picked_result = $picked_result ?: $conn->query("SELECT 1 FROM scrap_req WHERE 1=0");
     $rejected_result = $rejected_result ?: $conn->query("SELECT 1 FROM scrap_req WHERE 1=0");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Scrapify</title>
    <link rel="stylesheet" href="styles/admin_dashboard.css">
</head>
<body>

<div class="dashboard-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="admin_dashboard.php" class="logo-container">
                 <img class="logo-img" src="assets/WhatsApp_Image_2025-10-14_at_12.58.19_efe28561-removebg-preview.png" alt="Scrapify Logo">
                <h1 class="logo-text">Scrapify Admin</h1>
            </a>
        </div>
        <nav class="sidebar-nav">
            <a class="tab-link" href="#" data-tab="pending">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Pending
            </a>
            <a class="tab-link" href="#" data-tab="approved">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Approved
            </a>
            <a class="tab-link" href="#" data-tab="picked">
                 <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Picked Up
            </a>
            <a class="tab-link" href="#" data-tab="rejected">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                Rejected
            </a>
        </nav>
         <div class="sidebar-footer">
             <a class="logout-link" href="admin_logout.php">
                 <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                 Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <header class="main-header">
            <h2 id="header-title" class="header-title">Pending Requests</h2>
        </header>
        <main class="main-area">

            <?php if (isset($dashboard_error)): ?>
                <div class="error-alert">
                    <strong>Error:</strong>
                    <span><?php echo htmlspecialchars($dashboard_error); ?></span>
                </div>
            <?php endif; ?>

            <?php
            function display_request_card($row, $actions = '') {
                ?>
                <div class="request-card">
                    <div class="card-img-container">
                         <img src="<?php echo (!empty($row['image_path']) && file_exists($row['image_path'])) ? htmlspecialchars($row['image_path']) : 'https://placehold.co/150x150/E2E8F0/4A5568?text=No+Image'; ?>"
                              alt="Scrap Image" class="card-img">
                    </div>
                    <div class="card-body">
                        <p><strong class="label">User:</strong> <?php echo htmlspecialchars($row['user_name']); ?> (<?php echo htmlspecialchars($row['user_phone']); ?>)</p>
                        <p><strong class="label">Material:</strong> <?php echo htmlspecialchars($row['material']); ?> (<?php echo htmlspecialchars($row['weight']); ?> kg)</p>
                        <p><strong class="label">Address:</strong> <?php echo htmlspecialchars($row['pick_address']); ?></p>
                        <p class="date"><strong>Requested:</strong> <?php echo htmlspecialchars($row['request_date']); ?></p>
                        <?php if(!empty($row['approved_date'])): ?>
                            <p class="date"><strong>Approved:</strong> <?php echo htmlspecialchars($row['approved_date']); ?></p>
                        <?php endif; ?>
                         <?php if(!empty($row['picked_date'])): ?>
                            <p class="date"><strong>Picked:</strong> <?php echo htmlspecialchars($row['picked_date']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($actions)): ?>
                        <div class="card-actions">
                            <?php echo $actions; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
            }
            ?>

            <div id="pending" class="tab-content">
                 <div class="grid-container">
                    <?php if ($pending_result->num_rows === 0): ?>
                        <p class="empty-message">No pending requests found.</p>
                    <?php else: ?>
                        <?php while ($row = $pending_result->fetch_assoc()):
                            $actions = '<form action="approve_request.php" method="POST" class="action-form"><input type="hidden" name="req_id" value="' . $row['req_id'] . '"><button class="btn btn-approve">Approve</button></form>';
                            $actions .= '<form action="reject_request.php" method="POST" class="action-form"><input type="hidden" name="req_id" value="' . $row['req_id'] . '"><button class="btn btn-reject">Reject</button></form>';
                            display_request_card($row, $actions);
                        ?>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div id="approved" class="tab-content">
                 <div class="grid-container">
                     <?php if ($approved_result->num_rows === 0): ?>
                        <p class="empty-message">No approved requests found.</p>
                    <?php else: ?>
                        <?php while ($row = $approved_result->fetch_assoc()):
                            $actions = '<form action="mark_picked.php" method="POST" class="action-form-full"><input type="hidden" name="req_id" value="' . $row['req_id'] . '"><button class="btn btn-picked">Mark as Picked</button></form>';
                            display_request_card($row, $actions);
                        ?>
                        <?php endwhile; ?>
                    <?php endif; ?>
                 </div>
            </div>

            <div id="picked" class="tab-content">
                <div class="grid-container">
                    <?php if ($picked_result->num_rows === 0): ?>
                        <p class="empty-message">No picked up requests found.</p>
                    <?php else: ?>
                        <?php while ($row = $picked_result->fetch_assoc()):
                            display_request_card($row);
                        ?>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

             <div id="rejected" class="tab-content">
                <div class="grid-container">
                     <?php if ($rejected_result->num_rows === 0): ?>
                        <p class="empty-message">No rejected requests found.</p>
                    <?php else: ?>
                        <?php while ($row = $rejected_result->fetch_assoc()):
                            display_request_card($row);
                        ?>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabLinks = document.querySelectorAll('.tab-link');
        const tabContents = document.querySelectorAll('.tab-content');
        const headerTitle = document.getElementById('header-title');

        function getActiveTabFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            const validTabs = ['pending', 'approved', 'picked', 'rejected'];
            return validTabs.includes(tabParam) ? tabParam : 'pending';
        }

        const currentTab = getActiveTabFromURL();

        function switchTab(tabId) {
            tabLinks.forEach(l => l.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            const activeLink = document.querySelector(`.tab-link[data-tab="${tabId}"]`);
            const activeContent = document.getElementById(tabId);

            if(activeLink) {
                activeLink.classList.add('active');
                headerTitle.textContent = `${activeLink.textContent.trim()} Requests`;
            } else {
                 headerTitle.textContent = 'Dashboard';
            }

            if(activeContent) {
                activeContent.classList.add('active');
            }
        }

        switchTab(currentTab);

        tabLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const tabId = this.dataset.tab;
                if (tabId) {
                    switchTab(tabId);
                    history.pushState({ tab: tabId }, '', `?tab=${tabId}`);
                }
            });
        });

        window.addEventListener('popstate', function(event) {
            const stateTab = event.state ? event.state.tab : getActiveTabFromURL();
             switchTab(stateTab);
        });
    });
</script>

</body>
</html>