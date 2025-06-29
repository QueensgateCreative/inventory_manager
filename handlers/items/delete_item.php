<?php
require_once '../../config.php';
require_login();

if (isset($_GET['item_id']) && isset($_GET['box_id'])) {
    $item_id = $_GET['item_id'];
    $box_id = $_GET['box_id'];

    // First, fetch the item to get its photo path (if any) and ensure ownership
    $stmt = $pdo->prepare("SELECT photo_path FROM items WHERE item_id = ? AND box_id = ?");
    $stmt->execute([$item_id, $box_id]);
    $item = $stmt->fetch();

    if ($item) {
        // Delete the item from the database
        $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = ? AND box_id = ?");
        try {
            $stmt->execute([$item_id, $box_id]);

            // If a photo exists, delete the file from the server
            if ($item['photo_path'] && file_exists(__DIR__ . '/../..' . $item['photo_path'])) {
                unlink(__DIR__ . '/../..' . $item['photo_path']);
            }

            header("Location: /inventory_manager/public/boxes/view.php?box_id=$box_id");
            exit;
        } catch (PDOException $e) {
            error_log("Database error deleting item: " . $e->getMessage());
            die('Failed to delete item.');
        }
    } else {
        die('Item not found or you do not have permission to delete it from this box.');
    }
} else {
    die('Item ID or Box ID not specified for deletion.');
}
?>
