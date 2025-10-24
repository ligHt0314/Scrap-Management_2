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
if ($stmt_user) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $stmt_user->bind_result($user_name);
    $stmt_user->fetch();
    $stmt_user->close();
} else {
    error_log("Failed to prepare user name statement: " . $conn->error);
}


$errors = [];
$success = false; // Flag to show success message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $material = trim($_POST['material']);
    $weight = trim($_POST['weight']);
    $description = trim($_POST['description']);
    $pick_address = trim($_POST['pick_address']);
    $request_date = date('Y-m-d H:i:s'); // Use DATETIME for more precision
    $status_id = 1; // Pending

    // --- Input Validation ---
    if (empty($material)) $errors[] = 'Please select a material type.';
    if (!is_numeric($weight) || $weight <= 0) $errors[] = 'Weight must be a positive number (e.g., 5.5).';
    if (empty($description)) $errors[] = 'Description is required.';
    if (strlen($description) > 500) $errors[] = 'Description cannot exceed 500 characters.'; // Add length limit
    if (empty($pick_address)) $errors[] = 'Pickup address is required.';
    if (strlen($pick_address) > 255) $errors[] = 'Pickup address cannot exceed 255 characters.'; // Add length limit

    // --- Image Upload Handling ---
    $image_path = null; // Default to NULL if no image
    if (!empty($_FILES['image']['name'])) {
        // Basic check for upload errors
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
             $errors[] = 'Error uploading file. Code: ' . $_FILES['image']['error'];
        } else {
            $target_dir = "uploads/";
            // Ensure uploads directory exists and is writable
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0755, true)) {
                    $errors[] = 'Failed to create uploads directory.';
                }
            } elseif(!is_writable($target_dir)) {
                 $errors[] = 'Uploads directory is not writable.';
            }

            if (empty($errors)) { // Proceed only if directory is okay
                $image_name = uniqid('img_', true) . '.' . strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION)); // More unique name
                $target_file = $target_dir . $image_name;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                // Check file type and size
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif']; // Allow GIF?
                $max_size = 5 * 1024 * 1024; // 5MB

                if (!in_array($imageFileType, $allowed_types)) {
                    $errors[] = 'Invalid file type. Only JPG, JPEG, PNG, GIF allowed.';
                } elseif ($_FILES['image']['size'] > $max_size) {
                    $errors[] = 'Image size exceeds the 5MB limit.';
                } else {
                    // Attempt to move the file
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_path = $target_file; // Set path only on successful upload
                    } else {
                        $errors[] = 'Failed to move uploaded file. Check permissions.';
                    }
                }
            }
        }
    } // End image upload check


    // --- Database Insertion ---
    if (empty($errors)) {
        $stmt_insert = $conn->prepare("INSERT INTO scrap_req (material, weight, description, image_path, pick_address, request_date, user_id, status_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if($stmt_insert) {
            // Correct bind_param types: d=double, s=string, i=integer
            $stmt_insert->bind_param('sdssssii', $material, $weight, $description, $image_path, $pick_address, $request_date, $user_id, $status_id);

            if ($stmt_insert->execute()) {
                $success = true; // Set success flag
                // Clear potentially sensitive POST data after successful submission
                $_POST = array();
            } else {
                $errors[] = "Database error: Could not submit request. " . $stmt_insert->error;
                error_log("DB Insert Error: " . $stmt_insert->error); // Log error
            }
            $stmt_insert->close();
        } else {
             $errors[] = "Database error: Failed to prepare statement.";
             error_log("DB Prepare Error: " . $conn->error);
        }
    }
    // No $conn->close() here if submitting the form, needed for user name fetch on reload
} else {
    // Close connection if not POST request (initial page load)
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Scrap - Scrapify</title>
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
         /* Style for file input */
        input[type="file"]::file-selector-button { /* Standard */
            margin-right: 1rem; padding: 0.5rem 1rem; border-radius: 9999px; border-width: 0px; font-size: 0.875rem; font-weight: 600; background-color: #ECFDF5; color: #065F46; cursor: pointer;
        }
        input[type="file"]::file-selector-button:hover { background-color: #D1FAE5; }

        input[type="file"]::-webkit-file-upload-button { /* Safari/Chrome specific */
             margin-right: 1rem; padding: 0.5rem 1rem; border-radius: 9999px; border-width: 0px; font-size: 0.875rem; font-weight: 600; background-color: #ECFDF5; color: #065F46; cursor: pointer; -webkit-appearance: none; appearance: none;
        }
         input[type="file"]::-webkit-file-upload-button:hover { background-color: #D1FAE5; }


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
             <!-- FIX: Make Dashboard link visible -->
            <a class="sidebar-link flex items-center mt-2 py-3 px-6 rounded-l-lg" href="home.php">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            <!-- FIX: Sell Scrap marked as active -->
            <a class="sidebar-link active flex items-center mt-2 py-3 px-6 rounded-l-lg" href="sell_scrap.php">
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
            <h2 class="text-2xl font-semibold text-gray-700">Submit a Scrap Request</h2>
            <div class="flex items-center">
                 <span class="mr-3 text-sm font-medium text-gray-600"><?php echo htmlspecialchars($user_name); ?></span>
                 <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-300" src="https://placehold.co/100x100/E2E8F0/4A5568?text=<?php echo htmlspecialchars(strtoupper(substr($user_name, 0, 1))); ?>" alt="User avatar">
            </div>
        </header>
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold mb-6 text-gray-800">Enter Scrap Details</h3>

                <?php if(!empty($errors)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6" role="alert">
                    <p class="font-bold mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-sm">
                        <?php foreach($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if($success): ?>
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-6 rounded-lg text-center shadow-sm" role="alert">
    <div class="flex justify-center items-center mb-3">
        <svg class="w-8 h-8 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="font-bold text-lg">Success!</p>
    </div>
    <p>Your scrap request has been submitted successfully.</p>
    <div class="mt-6 flex flex-col sm:flex-row justify-center items-center gap-4">
        <a href="sell_scrap.php" class="inline-block w-full sm:w-auto px-6 py-2.5 bg-green-600 text-white font-medium text-sm leading-tight uppercase rounded-md shadow-md hover:bg-green-700 hover:shadow-lg focus:bg-green-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-green-800 active:shadow-lg transition duration-150 ease-in-out">
            Submit Another Request
        </a>
        <a href="past_request.php" class="w-full sm:w-auto text-center font-semibold text-green-800 hover:underline">
            View My Requests &rarr;
        </a>
    </div>
</div>
 <?php else: ?>
                    <form action="sell_scrap.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label for="material" class="block text-sm font-medium text-gray-700 mb-1">Material Type <span class="text-red-500">*</span></label>
                            <select id="material" name="material" required class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md shadow-sm">
                                <option value="">Select Material</option>
                                <option value="Paper" <?php echo (isset($_POST['material']) && $_POST['material'] == 'Paper') ? 'selected' : ''; ?>>Paper</option>
                                <option value="Cardboard" <?php echo (isset($_POST['material']) && $_POST['material'] == 'Cardboard') ? 'selected' : ''; ?>>Cardboard</option>
                                <option value="Plastic" <?php echo (isset($_POST['material']) && $_POST['material'] == 'Plastic') ? 'selected' : ''; ?>>Plastic</option>
                                <option value="Iron" <?php echo (isset($_POST['material']) && $_POST['material'] == 'Iron') ? 'selected' : ''; ?>>Iron</option>
                                <option value="Steel" <?php echo (isset($_POST['material']) && $_POST['material'] == 'Steel') ? 'selected' : ''; ?>>Steel</option>
                                <option value="Aluminum" <?php echo (isset($_POST['material']) && $_POST['material'] == 'Aluminum') ? 'selected' : ''; ?>>Aluminum</option>
                                <option value="Copper" <?php echo (isset($_POST['material']) && $_POST['material'] == 'Copper') ? 'selected' : ''; ?>>Copper</option>
                                <option value="E-Waste" <?php echo (isset($_POST['material']) && $_POST['material'] == 'E-Waste') ? 'selected' : ''; ?>>E-Waste</option>
                                <option value="Glass Bottles" <?php echo (isset($_POST['material']) && $_POST['material'] == 'Glass Bottles') ? 'selected' : ''; ?>>Glass Bottles</option>
                                <option value="Tires" <?php echo (isset($_POST['material']) && $_POST['material'] == 'Tires') ? 'selected' : ''; ?>>Tires</option>
                            </select>
                        </div>
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Approx. Weight (kg) <span class="text-red-500">*</span></label>
                            <input type="number" name="weight" id="weight" step="0.1" min="0.1" required placeholder="e.g., 5.5" value="<?php echo isset($_POST['weight']) ? htmlspecialchars($_POST['weight']) : ''; ?>" class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                            <textarea id="description" name="description" rows="3" required placeholder="e.g., Old newspapers, mixed plastic bottles, and cardboard boxes" class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            <p class="mt-1 text-xs text-gray-500">Max 500 characters.</p>
                        </div>
                         <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Upload Image (Optional, Max 5MB)</label>
                            <input type="file" name="image" id="image" accept="image/png, image/jpeg, image/jpg, image/gif" class="block w-full text-sm text-gray-500 border border-gray-300 rounded-md cursor-pointer focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                         </div>
                        <div>
                            <label for="pick_address" class="block text-sm font-medium text-gray-700 mb-1">Pickup Address <span class="text-red-500">*</span></label>
                            <input type="text" name="pick_address" id="pick_address" required placeholder="Enter your full pickup address" value="<?php echo isset($_POST['pick_address']) ? htmlspecialchars($_POST['pick_address']) : ''; ?>" class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                             <p class="mt-1 text-xs text-gray-500">Max 255 characters.</p>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">Submit Request</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

</body>
</html>

