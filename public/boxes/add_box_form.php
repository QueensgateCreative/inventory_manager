<?php
require_once '../../config.php';
require_login();

// echo '<!-- This file is part of the Inventory Manager project. -->';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Box - Inventory Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
</head>
<body class="bg-light p-3">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Add New Box</h1>
        <a href="../dashboard.php" class="btn btn-outline-secondary rounded-pill">&larr; Back to Dashboard</a>
    </div>

    <div id="scanner-container" class="mb-4" style="display: none;">
        <video id="scanner-video" playsinline style="width: 100%; max-width: 500px; border-radius: 15px;"></video>
        <button id="close-scanner" class="btn btn-danger mt-2">Close Scanner</button>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Box Details</h5>
        </div>
        <div class="card-body">
            <form action="../../handlers/boxes/add_box.php" method="POST" class="row g-3">
                <div class="col-md-6">
                    <label for="box_identifier" class="form-label">Box Identifier</label>
                    <div class="input-group">
                        <input type="text" name="box_identifier" id="box_identifier" class="form-control rounded-start-3" required placeholder="e.g., A001">
                        <button class="btn btn-outline-secondary" type="button" id="scan-qr-btn">Scan QR</button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="box_label" class="form-label">Box Label (Optional)</label>
                    <input type="text" name="box_label" id="box_label" class="form-control rounded-3" placeholder="e.g., Living Room Items">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Add Box</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const scannerVideo = document.getElementById('scanner-video');
    const scanQrBtn = document.getElementById('scan-qr-btn');
    const closeScannerBtn = document.getElementById('close-scanner');
    const scannerContainer = document.getElementById('scanner-container');
    const boxIdentifierInput = document.getElementById('box_identifier');
    let stream;

    scanQrBtn.addEventListener('click', () => {
        scannerContainer.style.display = 'block';
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(s => {
                stream = s;
                scannerVideo.srcObject = stream;
                scannerVideo.play();
                requestAnimationFrame(tick);
            })
            .catch(err => {
                console.error("Error accessing camera:", err);
                alert("Could not access the camera. Please ensure you've granted permission.");
            });
    });

    closeScannerBtn.addEventListener('click', () => {
        scannerContainer.style.display = 'none';
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });

    function tick() {
        if (scannerVideo.readyState === scannerVideo.HAVE_ENOUGH_DATA) {
            const canvasElement = document.createElement('canvas');
            const canvas = canvasElement.getContext('2d');
            canvasElement.height = scannerVideo.videoHeight;
            canvasElement.width = scannerVideo.videoWidth;
            canvas.drawImage(scannerVideo, 0, 0, canvasElement.width, canvasElement.height);
            const imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "dontInvert",
            });

            if (code) {
                boxIdentifierInput.value = code.data;
                closeScannerBtn.click();
            }
        }
        requestAnimationFrame(tick);
    }
</script>
</body>
</html>
