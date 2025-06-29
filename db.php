<?php
// db.php

$host = APP_DEBUG ? 'localhost' : 'production_host';
$db   = 'inventory_manager';
$user = APP_DEBUG ? 'root' : 'prod_user';
$pass = APP_DEBUG ? '' : 'prod_pass';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Database connection failed.');
}