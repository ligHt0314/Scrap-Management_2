<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = ''; // Initialize

// Fetch user's name using prepared statement
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


// Fetch user's requests using prepared statement
$query = "SELECT r.req_id, r.material, r.weight, r.image_path, r.pick_address, r.request_date, r.approved_date, r.picked_date, rs.status_name
          FROM scrap_req r
          JOIN req_status rs ON r.status_id = rs.status_id
          WHERE r.user_id = ?
          ORDER BY r.request_date DESC"; // Order by request date descending
$stmt = $conn->prepare($query);
$result = null; // Initialize result
if($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result(); // Get result set object
    $stmt->close();
} else {
    error_log("Failed to prepare requests statement: " . $conn->error);
}


// Define status colors for styling
$status_colors = [
    'requested' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
    'approved' => 'bg-green-100 text-green-800 border-green-300',
    'picked' => 'bg-blue-100 text-blue-800 border-blue-300',
    'rejected' => 'bg-red-100 text-red-800 border-red-300'
];

$conn->close(); // Close connection after fetching data
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Scrap Requests - Scrapify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
         /* Active sidebar link style */
        .sidebar-link.active {
            background-color: #E6F4EA;
            color: #166534;
            font-weight: 600;
        }
        .sidebar-link.active svg { color: #16A34A; }
         /* Default sidebar link style */
        .sidebar-link { color: #6B7280; }
        .sidebar-link:hover { background-color: #F3F4F6; color: #1F2937; }
         /* Add line-clamp for address */
        .line-clamp-2 {
             overflow: hidden;
             display: -webkit-box;
             -webkit-box-orient: vertical;
             -webkit-line-clamp: 2;
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div class="w-64 bg-white shadow-md flex flex-col">
        <div class="p-6">
             <a href="home.php" class="flex items-center">
                <img class="h-8 w-auto mr-2" src="assets/WhatsApp_Image_2025-10-14_at_12.58.19_efe28561-removebg-preview.png" alt="Scrapify Logo">
                <h1 class="text-2xl font-bold text-green-600">Scrapify</h1>
            </a>
        </div>
        <!-- Consistent Navigation Structure -->
        <nav class="mt-6 flex-1 overflow-y-auto">
            <a class="sidebar-link flex items-center mt-2 py-3 px-6 rounded-l-lg" href="home.php">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            <a class="sidebar-link flex items-center mt-2 py-3 px-6 rounded-l-lg" href="sell_scrap.php">
                 <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01M12 6v-1m0 1V4m0 2.01V5M12 21a9 9 0 110-18 9 9 0 010 18z"></path></svg>
                Sell Scrap
            </a>
            <!-- My Requests marked as active -->
            <a class="sidebar-link active flex items-center mt-2 py-3 px-6 rounded-l-lg" href="past_request.php">
                 <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                My Requests
            </a>
        </nav>
        <!-- Logout Button Consistently at Bottom -->
        <div class="p-6 mt-auto border-t border-gray-200">
             <a class="flex items-center py-3 px-4 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200" href="logout.php">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Logout
            </a>
        </div>
    </div>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="flex justify-between items-center p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-700">My Scrap Requests</h2>
             <div class="flex items-center">
                 <span class="mr-3 text-sm font-medium text-gray-600"><?php echo htmlspecialchars($user_name); ?></span>
                 <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-300" src="https://placehold.co/100x100/E2E8F0/4A5568?text=<?php echo htmlspecialchars(strtoupper(substr($user_name, 0, 1))); ?>" alt="User avatar">
            </div>
        </header>
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">

             <!-- Check if $result is valid before trying to access properties -->
            <?php if ($result === null): ?>
                 <p class="text-red-600 col-span-full">Error loading requests. Please try again later.</p>
            <?php elseif ($result->num_rows === 0): ?>
                <div class="text-center py-10 bg-white rounded-lg shadow">
                     <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                     </svg>
                     <h3 class="mt-2 text-sm font-medium text-gray-900">No requests yet</h3>
                    <p class="mt-1 text-sm text-gray-500">You haven't made any scrap requests. Get started by selling some scrap!</p>
                    <div class="mt-6">
                       <a href="sell_scrap.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                           Sell Scrap Now
                       </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"> <!-- More columns for better layout -->
                    <?php while ($row = $result->fetch_assoc()):
                        // Check if image exists, otherwise use placeholder
                        $image_display_path = (!empty($row['image_path']) && file_exists($row['image_path']))
                            ? htmlspecialchars($row['image_path'])
                            : 'https://placehold.co/600x400/E2E8F0/CBD5E0?text=No+Image';
                        // Get status color, default to gray if unknown
                        $status_class = $status_colors[strtolower($row['status_name'])] ?? 'bg-gray-100 text-gray-800 border-gray-300';
                    ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col transition duration-300 ease-in-out hover:shadow-lg">
                        <img src="<?php echo $image_display_path; ?>" alt="<?php echo htmlspecialchars($row['material']); ?> Scrap" class="w-full h-48 object-cover flex-shrink-0">
                        <div class="p-4 flex flex-col flex-grow">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-base font-semibold text-gray-800 leading-tight">
                                    <?php echo htmlspecialchars($row['material']); ?>
                                    <span class="text-sm font-normal text-gray-500">(<?php echo htmlspecialchars($row['weight']); ?> kg)</span>
                                </h3>
                                <span class="flex-shrink-0 ml-2 px-2.5 py-0.5 text-xs font-semibold border <?php echo $status_class; ?> rounded-full capitalize">
                                    <?php echo htmlspecialchars($row['status_name']); ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mb-3 line-clamp-2" title="<?php echo htmlspecialchars($row['pick_address']); ?>">
                                <strong>Address:</strong> <?php echo htmlspecialchars($row['pick_address']); ?>
                            </p>
                            <div class="text-xs text-gray-500 mt-auto border-t border-gray-200 pt-2 space-y-1">
                                <p><strong>Requested:</strong> <?php echo date("M d, Y", strtotime($row['request_date'])); // Format date ?></p>
                                <p><strong>Approved:</strong> <?php echo !empty($row['approved_date']) ? date("M d, Y", strtotime($row['approved_date'])) : "<span class='text-gray-400'>N/A</span>"; ?></p>
                                <p><strong>Picked:</strong> <?php echo !empty($row['picked_date']) ? date("M d, Y", strtotime($row['picked_date'])) : "<span class='text-gray-400'>N/A</span>"; ?></p>
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

