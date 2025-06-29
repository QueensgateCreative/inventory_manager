<?php
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
    try {
        $stmt->execute([$email, $password]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        header('Location: /inventory_manager/public/dashboard.php');
        exit;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        die('Registration failed.');
    }
}