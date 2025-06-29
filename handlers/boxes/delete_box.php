<?php
require_once '../../config.php';
require_login();

if (isset($_GET['box_id'])) {
    $box_id = $_GET['box_id'];

    // Start a transaction for atomicity
    $pdo->beginTransaction();

    try {
        // 1. Fetch all items in the box to delete their associated photos
        $stmt_items = $pdo->prepare("SELECT item_id, photo_path FROM items WHERE box_id = ?");
        $stmt_items->execute([$box_id]);
        $items_to_delete = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items_to_delete as $item) {
            if ($item['photo_path'] && file_exists(__DIR__ . '/../..' . $item['photo_path'])) {
                unlink(__DIR__ . '/../..' . $item['photo_path']);
            }
        }

        // 2. Delete all items associated with this box from the database
        $stmt_delete_items = $pdo->prepare("DELETE FROM items WHERE box_id = ?");
        $stmt_delete_items->execute([$box_id]);

        // 3. Delete the box itself
        $stmt_delete_box = $pdo->prepare("DELETE FROM boxes WHERE box_id = ? AND user_id = ?");
        $stmt_delete_box->execute([$box_id, $_SESSION['user_id']]);

        // Commit the transaction
        $pdo->commit();

        header("Location: /inventory_manager/public/dashboard.php");
        exit;

    } catch (PDOException $e) {
        // Rollback the transaction on error
        $pdo->rollBack();
        error_log("Database error deleting box and items: " . $e->getMessage());
        die('Failed to delete box and its items.');
    }
} else {
    die('Box ID not specified for deletion.');
}
?>
