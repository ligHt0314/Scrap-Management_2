<?php
session_start();
include 'config.php';

// BUG FIX: Strict check for admin session
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Function to fetch requests safely using prepared statements
function fetch_requests($conn, $status_id) {
    // BUG FIX: Use prepared statements to prevent SQL injection
    $sql = "SELECT r.req_id, r.material, r.weight, r.pick_address, r.request_date, r.approved_date, r.picked_date, r.image_path,
                   u.name AS user_name, u.phone AS user_phone
            FROM scrap_req r
            JOIN users u ON r.user_id = u.user_id
            WHERE r.status_id = ?
            ORDER BY r.request_date DESC"; // Consistent ordering
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed for status " . $status_id . ": (" . $conn->errno . ") " . $conn->error);
        return false; // Return false on error
    }
    $stmt->bind_param("i", $status_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

$pending_result = fetch_requests($conn, 1);
$approved_result = fetch_requests($conn, 2);
$picked_result = fetch_requests($conn, 4); // Status 4 is picked
$rejected_result = fetch_requests($conn, 3);

// Handle potential fetch errors
if ($pending_result === false || $approved_result === false || $picked_result === false || $rejected_result === false) {
     // Optionally display an error message or log it
     $dashboard_error = "Error fetching some request data. Please try again later.";
     // For simplicity, we'll try to continue with potentially empty result sets
     $pending_result = $pending_result ?: $conn->query("SELECT 1 FROM scrap_req WHERE 1=0"); // Empty result set
     $approved_result = $approved_result ?: $conn->query("SELECT 1 FROM scrap_req WHERE 1=0");
     $picked_result = $picked_result ?: $conn->query("SELECT 1 FROM scrap_req WHERE 1=0");
     $rejected_result = $rejected_result ?: $conn->query("SELECT 1 FROM scrap_req WHERE 1=0");
}

$conn->close(); // Close connection after fetching all data
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Scrapify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        /* IMPROVEMENT: Better active tab styling */
        .tab-link.active {
            background-color: #4A5568; /* gray-700 */
            color: #ffffff;
            font-weight: 600;
        }
        .tab-link:not(.active) {
             color: #A0AEC0; /* gray-500 */
        }
        .tab-link:not(.active):hover {
            background-color: #2D3748; /* gray-800 */
            color: #E2E8F0; /* gray-300 */
        }
        /* IMPROVEMENT: Ensure consistent card height for alignment */
        .request-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%; /* Make card fill grid item height */
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="flex h-screen bg-gray-100 overflow-hidden"> <!-- Prevent body scroll -->
    <!-- Sidebar -->
    <!-- FIX: Updated sidebar structure -->
    <div class="w-64 bg-gray-900 text-gray-300 flex flex-col">
        <div class="p-6">
            <a href="admin_dashboard.php" class="flex items-center">
                 <img class="h-8 w-auto mr-2" src="assets/WhatsApp_Image_2025-10-14_at_12.58.19_efe28561-removebg-preview.png" alt="Scrapify Logo">
                <h1 class="text-2xl font-bold text-green-500">Scrapify Admin</h1>
            </a>
        </div>
        <nav class="mt-6 flex-1 overflow-y-auto"> <!-- Navigation items first -->
            <a class="tab-link flex items-center mt-2 py-3 px-6 rounded-l-lg" href="#" data-tab="pending">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Pending
            </a>
            <a class="tab-link flex items-center mt-2 py-3 px-6 rounded-l-lg" href="#" data-tab="approved">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Approved
            </a>
            <a class="tab-link flex items-center mt-2 py-3 px-6 rounded-l-lg" href="#" data-tab="picked">
                 <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <!-- Reusing icon, replace if needed -->
                Picked Up
            </a>
            <a class="tab-link flex items-center mt-2 py-3 px-6 rounded-l-lg" href="#" data-tab="rejected">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                Rejected
            </a>
        </nav>
         <!-- FIX: Logout button moved to the bottom -->
         <div class="p-6 mt-auto border-t border-gray-700"> <!-- Adjusted border color -->
             <a class="flex items-center py-3 px-4 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200" href="admin_logout.php">
                 <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                 Logout
            </a>
        </div>
    </div>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="flex justify-between items-center p-6 bg-white border-b border-gray-200">
            <h2 id="header-title" class="text-2xl font-semibold text-gray-700">Pending Requests</h2>
            <!-- Maybe add admin name or profile icon here later -->
        </header>
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">

            <?php if (isset($dashboard_error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error:</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($dashboard_error); ?></span>
                </div>
            <?php endif; ?>

            <?php
            // Function to display request cards to reduce repetition
            function display_request_card($row, $actions = '') {
                ?>
                <div class="bg-white p-4 rounded-lg shadow-md flex flex-col space-y-3 request-card"> <!-- Use request-card class -->
                    <div class="flex-shrink-0">
                         <img src="<?php echo (!empty($row['image_path']) && file_exists($row['image_path'])) ? htmlspecialchars($row['image_path']) : 'https://placehold.co/150x150/E2E8F0/4A5568?text=No+Image'; ?>"
                              alt="Scrap Image"
                              class="w-full h-32 rounded-md object-cover mb-3"> <!-- Image fills width -->
                    </div>
                    <div class="flex-grow space-y-1 text-sm"> <!-- Smaller text -->
                        <p><strong class="font-semibold text-gray-700">User:</strong> <?php echo htmlspecialchars($row['user_name']); ?> (<?php echo htmlspecialchars($row['user_phone']); ?>)</p>
                        <p><strong class="font-semibold text-gray-700">Material:</strong> <?php echo htmlspecialchars($row['material']); ?> (<?php echo htmlspecialchars($row['weight']); ?> kg)</p>
                        <p><strong class="font-semibold text-gray-700">Address:</strong> <?php echo htmlspecialchars($row['pick_address']); ?></p>
                        <p class="text-xs text-gray-500"><strong>Requested:</strong> <?php echo htmlspecialchars($row['request_date']); ?></p>
                        <?php if(!empty($row['approved_date'])): ?>
                            <p class="text-xs text-gray-500"><strong>Approved:</strong> <?php echo htmlspecialchars($row['approved_date']); ?></p>
                        <?php endif; ?>
                         <?php if(!empty($row['picked_date'])): ?>
                            <p class="text-xs text-gray-500"><strong>Picked:</strong> <?php echo htmlspecialchars($row['picked_date']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($actions)): ?>
                        <div class="flex space-x-2 mt-auto pt-2 border-t border-gray-200"> <!-- Actions at bottom -->
                            <?php echo $actions; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
            }
            ?>

            <!-- Pending Tab -->
            <div id="pending" class="tab-content">
                 <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"> <!-- More columns on larger screens -->
                    <?php if ($pending_result->num_rows === 0): ?>
                        <p class="text-gray-500 col-span-full">No pending requests found.</p>
                    <?php else: ?>
                        <?php while ($row = $pending_result->fetch_assoc()):
                            $actions = '<form action="approve_request.php" method="POST" class="flex-1"><input type="hidden" name="req_id" value="' . $row['req_id'] . '"><button class="w-full px-3 py-1 text-xs font-medium text-white bg-green-500 rounded hover:bg-green-600">Approve</button></form>';
                            $actions .= '<form action="reject_request.php" method="POST" class="flex-1"><input type="hidden" name="req_id" value="' . $row['req_id'] . '"><button class="w-full px-3 py-1 text-xs font-medium text-white bg-red-500 rounded hover:bg-red-600">Reject</button></form>';
                            display_request_card($row, $actions);
                        ?>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Approved Tab -->
            <div id="approved" class="tab-content">
                 <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                     <?php if ($approved_result->num_rows === 0): ?>
                        <p class="text-gray-500 col-span-full">No approved requests found.</p>
                    <?php else: ?>
                        <?php while ($row = $approved_result->fetch_assoc()):
                            $actions = '<form action="mark_picked.php" method="POST" class="w-full"><input type="hidden" name="req_id" value="' . $row['req_id'] . '"><button class="w-full px-3 py-1 text-xs font-medium text-white bg-blue-500 rounded hover:bg-blue-600">Mark as Picked</button></form>';
                            display_request_card($row, $actions);
                        ?>
                        <?php endwhile; ?>
                    <?php endif; ?>
                 </div>
            </div>

            <!-- Picked Tab -->
            <div id="picked" class="tab-content">
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <?php if ($picked_result->num_rows === 0): ?>
                        <p class="text-gray-500 col-span-full">No picked up requests found.</p>
                    <?php else: ?>
                        <?php while ($row = $picked_result->fetch_assoc()):
                            display_request_card($row); // No actions needed for picked
                        ?>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

             <!-- Rejected Tab -->
             <div id="rejected" class="tab-content">
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                     <?php if ($rejected_result->num_rows === 0): ?>
                        <p class="text-gray-500 col-span-full">No rejected requests found.</p>
                    <?php else: ?>
                        <?php while ($row = $rejected_result->fetch_assoc()):
                            display_request_card($row); // No actions needed for rejected
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

        // Function to safely get tab from URL
        function getActiveTabFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            const validTabs = ['pending', 'approved', 'picked', 'rejected'];
            // Return 'pending' if param is missing or invalid
            return validTabs.includes(tabParam) ? tabParam : 'pending';
        }

        const currentTab = getActiveTabFromURL();

        function switchTab(tabId) {
            // Remove active class from all links and contents
            tabLinks.forEach(l => l.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to the current link and content
            const activeLink = document.querySelector(`.tab-link[data-tab="${tabId}"]`);
            const activeContent = document.getElementById(tabId);

            if(activeLink) {
                activeLink.classList.add('active');
                // Update header title based on the active link's text
                headerTitle.textContent = `${activeLink.textContent.trim()} Requests`;
            } else {
                 headerTitle.textContent = 'Dashboard'; // Fallback title
            }

            if(activeContent) {
                activeContent.classList.add('active');
            }
        }

        // Initialize with the correct tab based on URL or default
        switchTab(currentTab);

        // Add event listeners to tab links
        tabLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const tabId = this.dataset.tab;
                if (tabId) { // Ensure data-tab attribute exists
                    switchTab(tabId);
                    // Update URL history without reloading the page
                    history.pushState({ tab: tabId }, '', `?tab=${tabId}`);
                }
            });
        });

        // Listen for browser back/forward navigation
        window.addEventListener('popstate', function(event) {
            const stateTab = event.state ? event.state.tab : getActiveTabFromURL();
             switchTab(stateTab);
        });
    });
</script>

</body>
</html>

