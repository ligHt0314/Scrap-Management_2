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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
         /* Active sidebar link style */
        .sidebar-link.active {
            background-color: #E6F4EA; /* Lighter green */
            color: #166534; /* Darker green text */
            font-weight: 600;
        }
        .sidebar-link.active svg {
             color: #16A34A; /* Medium green icon */
        }
         /* Default sidebar link style */
        .sidebar-link {
            color: #6B7280; /* Gray text */
        }
        .sidebar-link:hover {
             background-color: #F3F4F6; /* Light gray hover */
             color: #1F2937; /* Darker gray text on hover */
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div class="w-64 bg-white shadow-md flex flex-col">
        <div class="p-6">
             <!-- Logo linked to user dashboard home -->
             <a href="home.php" class="flex items-center">
                <img class="h-8 w-auto mr-2" src="assets/WhatsApp_Image_2025-10-14_at_12.58.19_efe28561-removebg-preview.png" alt="Scrapify Logo">
                <h1 class="text-2xl font-bold text-green-600">Scrapify</h1>
            </a>
        </div>
        <!-- Consistent Navigation Structure -->
        <nav class="mt-6 flex-1 overflow-y-auto">
            <!-- FIX: Added 'active' class to Dashboard link -->
            <a class="sidebar-link active flex items-center mt-2 py-3 px-6 rounded-l-lg" href="home.php">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            <a class="sidebar-link flex items-center mt-2 py-3 px-6 rounded-l-lg" href="sell_scrap.php">
                 <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01M12 6v-1m0 1V4m0 2.01V5M12 21a9 9 0 110-18 9 9 0 010 18z"></path></svg>
                Sell Scrap
            </a>
            <a class="sidebar-link flex items-center mt-2 py-3 px-6 rounded-l-lg" href="past_request.php">
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
            <h2 class="text-2xl font-semibold text-gray-700">Dashboard</h2>
             <div class="flex items-center">
                 <span class="mr-3 text-sm font-medium text-gray-600"><?php echo htmlspecialchars($user_name); ?></span>
                 <!-- Placeholder Avatar -->
                 <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-300" src="https://placehold.co/100x100/E2E8F0/4A5568?text=<?php echo htmlspecialchars(strtoupper(substr($user_name, 0, 1))); ?>" alt="User avatar">
            </div>
        </header>
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="mb-8">
                <h3 class="text-3xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h3>
                <p class="text-gray-600 mt-1">Here's a summary of your scrap selling activity.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Pending Requests Card -->
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded-lg shadow-md flex justify-between items-center" role="alert">
                     <div>
                        <p class="font-bold text-sm uppercase tracking-wider">Pending</p>
                        <p class="text-3xl font-semibold"><?php echo $pending_count; ?></p>
                     </div>
                     <svg class="w-8 h-8 text-yellow-500 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>

                <!-- Approved Pickups Card -->
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded-lg shadow-md flex justify-between items-center" role="alert">
                    <div>
                        <p class="font-bold text-sm uppercase tracking-wider">Approved</p>
                        <p class="text-3xl font-semibold"><?php echo $approved_count; ?></p>
                    </div>
                    <svg class="w-8 h-8 text-green-500 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>

                <!-- History Card -->
                 <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-800 p-4 rounded-lg shadow-md flex justify-between items-center" role="alert">
                     <div>
                        <p class="font-bold text-sm uppercase tracking-wider">History</p>
                        <p class="text-3xl font-semibold"><?php echo $history_count; ?></p>
                     </div>
                      <svg class="w-8 h-8 text-blue-500 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg> <!-- Placeholder icon -->
                </div>
            </div>

            <!-- Call to Action -->
            <div class="mt-12 bg-white p-8 rounded-lg shadow-md text-center">
                <h3 class="text-2xl font-bold text-gray-800">Ready to Sell More Scrap?</h3>
                <p class="text-gray-600 mt-2 mb-6">It's quick and easy. Get the best rates for your scrap today!</p>
                <a href="sell_scrap.php" class="inline-flex items-center bg-green-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-700 transition duration-300 shadow-md">
                     <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01M12 6v-1m0 1V4m0 2.01V5M12 21a9 9 0 110-18 9 9 0 010 18z"></path></svg>
                    SELL SCRAP NOW
                </a>
            </div>

        </main>
    </div>
</div>

</body>
</html>

