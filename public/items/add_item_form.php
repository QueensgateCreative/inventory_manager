<?php
require_once '../../config.php';
require_login();

$box_id = $_GET['box_id'] ?? null;
if (!$box_id) {
    die('Box not specified.');
}

$stmt = $pdo->prepare("SELECT box_identifier, box_label FROM boxes WHERE box_id = ? AND user_id = ?");
$stmt->execute([$box_id, $_SESSION['user_id']]);
$box = $stmt->fetch();
if (!$box) {
    die('Box not found or you do not have permission to add items to it.');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item to Box: <?= htmlspecialchars($box['box_identifier']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>
    <style>
        #scanner-viewport { position: relative; width: 100%; max-width: 640px; margin: 0 auto; }
        #scanner-viewport video, #scanner-viewport canvas { width: 100%; height: auto; }
        #scanner-viewport canvas.drawingBuffer { position: absolute; top: 0; left: 0; }
        .spinner-border-sm { vertical-align: middle; }
    </style>
</head>
<body class="bg-light p-3">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Add New Item to <span class="text-primary"><?= htmlspecialchars($box['box_identifier']) ?></span></h2>
        <a href="../boxes/view.php?box_id=<?= $box_id ?>" class="btn btn-outline-secondary rounded-pill">&larr; Back to Box</a>
    </div>

    <div id="scanner-container" class="mb-4" style="display: none;">
        <div id="scanner-viewport"></div>
        <div id="status-message" class="alert alert-info mt-2">Initializing Scanner...</div>
        <button id="close-scanner" class="btn btn-danger mt-2">Close Scanner</button>
    </div>

    <div class="card shadow-sm mb-5">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Item Details</h5>
        </div>
        <div class="card-body">
            <form action="../../handlers/items/add_item.php" method="POST" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="box_id" value="<?= $box_id ?>">
                <div class="col-md-6">
                    <label for="item_name" class="form-label">Item Name</label>
                    <input type="text" name="item_name" id="item_name" class="form-control rounded-3" required>
                </div>
                <div class="col-md-6">
                    <label for="barcode" class="form-label">Barcode or ID (Optional)</label>
                    <div class="input-group">
                        <input type="text" name="barcode" id="barcode" class="form-control">
                        <button class="btn btn-outline-secondary" type="button" id="scan-barcode-btn">Scan</button>
                        <button class="btn btn-outline-primary" type="button" id="fetch-info-btn">
                            <span id="fetch-spinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                            Fetch Info
                        </button>
                    </div>
                </div>
                <div class="col-md-12">
                    <label for="photo" class="form-label">Photo (Optional)</label>
                    <input type="file" name="photo" id="photo" class="form-control rounded-3" accept="image/*" capture="environment">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- Scanner code ---
    const scanBarcodeBtn = document.getElementById('scan-barcode-btn');
    const closeScannerBtn = document.getElementById('close-scanner');
    const scannerContainer = document.getElementById('scanner-container');
    const barcodeInput = document.getElementById('barcode');
    const statusMessage = document.getElementById('status-message');
    scanBarcodeBtn.addEventListener('click', () => { scannerContainer.style.display = 'block'; startQuaggaScanner(); });
    closeScannerBtn.addEventListener('click', () => { scannerContainer.style.display = 'none'; Quagga.stop(); });
    function updateStatus(message, isError = false) { statusMessage.textContent = message; statusMessage.className = isError ? 'alert alert-danger mt-2' : 'alert alert-info mt-2'; }
    function startQuaggaScanner() {
        Quagga.init({
            inputStream: {name: "Live", type: "LiveStream", target: document.querySelector('#scanner-viewport'), constraints: {width: 640, height: 480, facingMode: "environment"}},
            locator: {patchSize: "medium", halfSample: true},
            decoder: {readers: ["code_128_reader", "ean_reader", "ean_8_reader", "code_39_reader", "codabar_reader", "upc_reader"]},
            locate: true,
        }, function (err) {
            if (err) { updateStatus(`Initialization Error: ${err.message}.`, true); return; }
            updateStatus("Camera started. Point at a barcode."); Quagga.start();
        });
        Quagga.onDetected(function(result) {
            barcodeInput.value = result.codeResult.code;
            closeScannerBtn.click();
            fetchProductInfo(); // Automatically fetch info after a successful scan
        });
    }

    // --- NEW Fetch Product Info Logic ---
    const fetchInfoBtn = document.getElementById('fetch-info-btn');
    const fetchSpinner = document.getElementById('fetch-spinner');
    const itemNameInput = document.getElementById('item_name');

    fetchInfoBtn.addEventListener('click', fetchProductInfo);

    function fetchProductInfo() {
        const barcode = barcodeInput.value.trim();
        if (!barcode) {
            alert("Please scan or enter a barcode first.");
            return;
        }

        fetchSpinner.style.display = 'inline-block';
        fetchInfoBtn.disabled = true;

        fetch(`../../handlers/items/fetch_barcode_data.php?barcode=${barcode}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Network response was not ok: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                // Check the structure of the response from UPCitemdb
                if (data.code === 'OK' && data.items && data.items.length > 0) {
                    const product = data.items[0];
                    // Prefer 'title', but fall back to 'description' or 'brand'
                    const productName = product.title || product.description || product.brand;
                    if (productName) {
                        itemNameInput.value = productName;
                        alert(`Product found: ${productName}`);
                    } else {
                        alert("Product found, but it has no name or description.");
                    }
                } else {
                    alert("Product not found in the database. You might have exceeded the daily free limit.");
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert("An error occurred while trying to fetch product information. Please check the console for details.");
            })
            .finally(() => {
                // This block will always run, whether the fetch succeeded or failed
                fetchSpinner.style.display = 'none';
                fetchInfoBtn.disabled = false;
            });
    }
</script>
</body>
</html>