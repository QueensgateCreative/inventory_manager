<?php
require_once '../../config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $box_id = $_POST['box_id'];
    $item_name = trim($_POST['item_name']);
    $barcode = trim($_POST['barcode']);

    $photo_path = null;
    if (!empty($_FILES['photo']['name'])) {
        $target_dir = __DIR__ . '/../../assets/uploads/items/';
        $filename = time() . '_' . basename($_FILES['photo']['name']);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $photo_path = '/inventory_manager/assets/uploads/items/' . $filename;
        } else {
            error_log("Failed to move uploaded file: " . $_FILES['photo']['tmp_name'] . " to " . $target_file);
        }
    }

    $stmt = $pdo->prepare("INSERT INTO items (box_id, item_name, barcode, photo_path) VALUES (?, ?, ?, ?)");
    try {
        $stmt->execute([$box_id, $item_name, $barcode, $photo_path]);
        header("Location: /inventory_manager/public/boxes/view.php?box_id=$box_id");
        exit;
    } catch (PDOException $e) {
        error_log("Database error adding item: " . $e->getMessage());
        die('Failed to add item.');
    }
}