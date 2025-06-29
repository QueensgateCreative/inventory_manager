<?php
require_once '../../config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $box_identifier = trim($_POST['box_identifier']);
    $box_label = trim($_POST['box_label']);

    $stmt = $pdo->prepare("INSERT INTO boxes (user_id, box_identifier, box_label) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $box_identifier, $box_label]);

    header('Location: /inventory_manager/public/dashboard.php');
    exit;
}