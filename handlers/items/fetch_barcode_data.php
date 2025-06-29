<?php
require_once '../../config.php';
require_login();

header('Content-Type: application/json');

$barcode = $_GET['barcode'] ?? '';

if (empty($barcode)) {
    http_response_code(400);
    echo json_encode(['error' => 'Barcode not provided.']);
    exit;
}

// Construct the URL for the UPCitemdb API
$url = "https://api.upcitemdb.com/prod/trial/lookup?upc=" . urlencode($barcode);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// It's good practice to set a custom User-Agent
curl_setopt($ch, CURLOPT_USERAGENT, 'MyInventoryApp/1.0 (php/curl)');
curl_close($ch);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpcode !== 200) {
    http_response_code($httpcode);
    echo json_encode(['error' => 'API request failed.']);
    exit;
}

// Send the raw response back to the front-end to be processed
echo $response;
?>