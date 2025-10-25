<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = '';

// Use a new connection for this query to avoid conflicts if $conn is closed elsewhere
$conn_for_user = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn_for_user->connect_error) {
    error_log("Connection failed: " . $conn_for_user->connect_error);
} else {
    $stmt_user = $conn_for_user->prepare("SELECT name FROM users WHERE user_id = ?");
    if ($stmt_user) {
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $stmt_user->bind_result($user_name);
        $stmt_user->fetch();
        $stmt_user->close();
    } else {
        error_log("Failed to prepare user name statement: " . $conn_for_user->error);
    }
    $conn_for_user->close();
}


$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Re-establish connection for POST logic
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        die("ERROR: Could not connect. " . $conn->connect_error);
    }

    $material = trim($_POST['material']);
    $weight = trim($_POST['weight']);
    $description = trim($_POST['description']);
    $pick_address = trim($_POST['pick_address']);
    $request_date = date('Y-m-d H:i:s');
    $status_id = 1; // Pending

    // --- Input Validation ---
    if (empty($material)) $errors[] = 'Please select a material type.';
    if (!is_numeric($weight) || $weight <= 0) $errors[] = 'Weight or quantity must be a positive number (e.g., 5.5).';
    if (empty($description)) $errors[] = 'Description is required.';
    if (strlen($description) > 500) $errors[] = 'Description cannot exceed 500 characters.';
    if (empty($pick_address)) $errors[] = 'Pickup address is required.';
    if (strlen($pick_address) > 255) $errors[] = 'Pickup address cannot exceed 255 characters.';

    // --- Image Upload Handling (NOW REQUIRED) ---
    $image_path = null; 
    if (empty($_FILES['image']['name'])) { // Check if it's EMPTY
        $errors[] = 'An image of the scrap is required.';
    } else { // It's not empty, so process it
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
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
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
    $conn->close(); // Close connection after POST logic
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Scrap - Scrapify</title>
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/form.css">
</head>
<body class="body-bg">

<div class="dashboard-container">
    <!-- Sidebar -->
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
            <a class="sidebar-link active" href="sell_scrap.php">
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

    <!-- Main content -->
    <div class="main-content">
        <header class="main-header">
            <h2 class="header-title">Submit a Scrap Request</h2>
            <div class="user-info">
                 <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                 <img class="user-avatar" src="https://placehold.co/100x100/E2E8F0/4A5568?text=<?php echo htmlspecialchars(strtoupper(substr($user_name, 0, 1))); ?>" alt="User avatar">
            </div>
        </header>
        <main class="main-area">
            <div class="form-container">
                <h3 class="form-title">Enter Scrap Details</h3>

                <?php if(!empty($errors)): ?>
                <div class="alert alert-error">
                    <p class="alert-title">Please fix the following errors:</p>
                    <ul class="alert-list">
                        <?php foreach($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if($success): ?>
                <div class="alert alert-success">
                    <div class="success-header">
                        <svg class="success-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="alert-title">Success!</p>
                    </div>
                    <p>Your scrap request has been submitted successfully.</p>
                    <div class="success-actions">
                        <a href="sell_scrap.php" class="button">Submit Another Request</a>
                        <a href="past_request.php" class="link">View My Requests &rarr;</a>
                    </div>
                </div>
                <?php else: ?>
                    <form action="sell_scrap.php" method="POST" enctype="multipart/form-data" class="form-grid">
                        <div class="form-group">
                            <label for="material" class="form-label">Material Type <span class="required">*</span></label>
                            <select id="material" name="material" required class="form-select">
                                <option value="">Select Material</option>
                                <option value="Newspaper">Newspaper</option>
                                <option value="Carton">Carton</option>
                                <option value="Mix Plastic">Mix Plastic</option>
                                <option value="Books">Books</option>
                                <option value="Iron">Iron</option>
                                <option value="Tin">Tin</option>
                                <option value="E-waste">E-waste</option>
                                <option value="Aluminium">Aluminium</option>
                                <option value="Magazines">Magazines</option>
                                <option value="Brass">Brass</option>
                                <option value="Copper">Copper</option>
                                <option value="Casting Aluminium">Casting Aluminium</option>
                                <option value="Television (LCD/LED)">Television (LCD/LED)</option>
                                <option value="AC">AC</option>
                                <option value="Refrigerator">Refrigerator</option>
                                <option value="CPU">CPU</option>
                                <option value="Geyser">Geyser</option>
                                <option value="Bottle">Bottle</option>
                                <option value="Printer">Printer</option>
                                <option value="UPS (with battery)">UPS (with battery)</option>
                                <option value="Microwave">Microwave</option>
                                <option value="Laptops">Laptops</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <!-- This label's text will be changed by JavaScript -->
                            <label for="weight" id="weight-label" class="form-label">Approx. Weight (kg) <span class="required">*</span></label>
                            <input type="number" name="weight" id="weight" step="0.1" min="0.1" required placeholder="e.g., 5.5" value="<?php echo isset($_POST['weight']) ? htmlspecialchars($_POST['weight']) : ''; ?>" class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description <span class="required">*</span></label>
                            <textarea id="description" name="description" rows="3" required placeholder="e.g., Old newspapers, mixed plastic bottles..." class="form-textarea"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            <p class="form-help-text">Max 500 characters.</p>
                        </div>
                         
                         <!-- UPDATED: Image field is now required -->
                         <div class="form-group">
                            <label for="image" class="form-label">Upload Image <span class="required">*</span> (Max 5MB)</label>
                            <input type="file" name="image" id="image" accept="image/png, image/jpeg, image/jpg, image/gif" class="form-file-input" required>
                         </div>

                        <div class="form-group">
                            <label for="pick_address" class="form-label">Pickup Address <span class="required">*</span></label>
                            <input type="text" name="pick_address" id="pick_address" required placeholder="Enter your full pickup address" value="<?php echo isset($_POST['pick_address']) ? htmlspecialchars($_POST['pick_address']) : ''; ?>" class="form-input">
                             <p class="form-help-text">Max 255 characters.</p>
                        </div>

                        <!-- NEW: Price Display Box -->
                        <div id="price-display" class="form-group" style="display: none;">
                            <label class="form-label">Approximate Price</label>
                            <div class="price-box">
                                <span id="price-value">₹ 0.00</span>
                                <span id="price-unit"></span>
                            </div>
                            <p class="form-help-text">This is an estimated price. The final price will be based on the actual weight and quality check during pickup.</p>
                        </div>
                        
                        <div class="form-footer">
                            <button type="submit" class="button button-full">Submit Request</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Define the rate list based on checkratelist.php
    const rateList = {
        'Newspaper': { price: 10, unit: 'kg' },
        'Carton': { price: 8, unit: 'kg' },
        'Mix Plastic': { price: 10, unit: 'kg' },
        'Books': { price: 12, unit: 'kg' },
        'Iron': { price: 26, unit: 'kg' },
        'Tin': { price: 5, unit: 'kg' },
        'E-waste': { price: 15, unit: 'kg' },
        'Aluminium': { price: 160, unit: 'kg' },
        'Magazines': { price: 10, unit: 'kg' },
        'Brass': { price: 450, unit: 'kg' },
        'Copper': { price: 700, unit: 'kg' },
        'Casting Aluminium': { price: 60, unit: 'kg' },
        'Television (LCD/LED)': { price: 50, unit: 'pcs' },
        'AC': { price: 3000, unit: 'pcs' },
        'Refrigerator': { price: 600, unit: 'pcs' },
        'CPU': { price: 150, unit: 'pcs' },
        'Geyser': { price: 50, unit: 'kg' },
        'Bottle': { price: 2, unit: 'kg' },
        'Printer': { price: 20, unit: 'pcs' },
        'UPS (with battery)': { price: 150, unit: 'pcs' },
        'Microwave': { price: 199, unit: 'pcs' },
        'Laptops': { price: 100, unit: 'pcs' }
    };

    // 2. Get references to the elements
    const materialSelect = document.getElementById('material');
    const weightInput = document.getElementById('weight');
    const weightLabel = document.getElementById('weight-label');
    const priceDisplay = document.getElementById('price-display');
    const priceValue = document.getElementById('price-value');
    const priceUnit = document.getElementById('price-unit');

    // 3. Create the calculation function
    function calculatePrice() {
        const material = materialSelect.value;
        const weight = parseFloat(weightInput.value);
        
        if (material && rateList[material] && weight > 0) {
            const item = rateList[material];
            const total = item.price * weight;
            
            priceValue.textContent = '₹ ' + total.toFixed(2);
            priceUnit.textContent = `@ ₹${item.price} / ${item.unit}`;
            priceDisplay.style.display = 'block';
        } else {
            priceDisplay.style.display = 'none';
        }
    }

    // 4. Create function to update label
    function updateLabel() {
        const material = materialSelect.value;
        if (material && rateList[material]) {
            const item = rateList[material];
            if (item.unit === 'pcs') {
                weightLabel.innerHTML = 'Approx. Quantity (pcs) <span class="required">*</span>';
                weightInput.step = '1'; // Only allow whole numbers for pcs
                weightInput.placeholder = 'e.g., 2';
            } else {
                weightLabel.innerHTML = 'Approx. Weight (kg) <span class="required">*</span>';
                weightInput.step = '0.1'; // Allow decimals for kg
                weightInput.placeholder = 'e.g., 5.5';
            }
        } else {
            // Default label
            weightLabel.innerHTML = 'Approx. Weight (kg) <span class="required">*</span>';
            weightInput.step = '0.1';
            weightInput.placeholder = 'e.g., 5.5';
        }
        // Recalculate price when label changes
        calculatePrice();
    }

    // 5. Add event listeners
    materialSelect.addEventListener('change', updateLabel);
    weightInput.addEventListener('input', calculatePrice);
});
</script>

</body>
</html>

