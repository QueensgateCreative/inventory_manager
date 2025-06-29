<?php
require_once '../../config.php';
require_login();

// Redirect if the page is accessed directly without a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../public/dashboard.php');
    exit();
}

// --- Data Collection and Validation ---
$item_name = trim($_POST['item_name'] ?? '');
$box_id = $_POST['box_id'] ?? null;
$photo_data_url = $_POST['photo_data_url'] ?? null; // For camera-captured photos
$user_id = $_SESSION['user_id']; // Still needed for the security check

if (empty($item_name) || empty($box_id)) {
    die("Error: Item name and box ID are required.");
}

// Security Check: Verify the logged-in user owns the box. This is very important.
$stmt = $pdo->prepare("SELECT box_id FROM boxes WHERE box_id = ? AND user_id = ?");
$stmt->execute([$box_id, $user_id]);
if ($stmt->rowCount() == 0) {
    die("Error: You do not have permission to modify this box.");
}

// --- Photo Handling ---
$photo_path = null;
$upload_dir = __DIR__ . '/../../assets/uploads/items/';
$db_path_prefix = '/inventory_manager/assets/uploads/items/';

if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        error_log("Failed to create upload directory: " . $upload_dir);
        die("A server configuration error occurred.");
    }
}

// Priority 1: Handle camera-captured photo
if (!empty($photo_data_url) && strpos($photo_data_url, 'data:image/') === 0) {
    $img_data = preg_replace('/^data:image\/\w+;base64,/', '', $photo_data_url);
    $img_data = base64_decode($img_data);
    $filename = uniqid('cam_', true) . '.jpg';
    $file_path = $upload_dir . $filename;

    if (file_put_contents($file_path, $img_data)) {
        $photo_path = $db_path_prefix . $filename;
    } else {
        error_log("Failed to save captured photo to " . $file_path);
    }
// Priority 2: Handle standard file upload
} elseif (isset($_FILES['photo_upload']) && $_FILES['photo_upload']['error'] === UPLOAD_ERR_OK) {
    $file_tmp_path = $_FILES['photo_upload']['tmp_name'];
    $file_name = $_FILES['photo_upload']['name'];
    $filename = time() . '_' . basename($file_name);
    $dest_path = $upload_dir . $filename;

    if (move_uploaded_file($file_tmp_path, $dest_path)) {
        $photo_path = $db_path_prefix . $filename;
    } else {
        error_log("Failed to move uploaded file to " . $dest_path);
    }
}

// --- Database Insertion (FIXED) ---
// The `user_id` column has been removed from this query to match your database structure.
try {
    $sql = "INSERT INTO items (item_name, box_id, photo_path) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    // The parameters array now correctly contains 3 items.
    $stmt->execute([$item_name, $box_id, $photo_path]);

    // Redirect to the box view on success
    header("Location: ../../public/boxes/view.php?box_id=" . $box_id);
    exit();

} catch (PDOException $e) {
    // This will log the specific SQL error to your server's error log for debugging,
    // which is very helpful if other issues arise.
    error_log("Database Error on item insert: " . $e->getMessage());
    die("An error occurred while adding the item to the database. Please try again.");
}
?>