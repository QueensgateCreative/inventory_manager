<?php
require_once '../../config.php';
require_login();

$box_id = $_GET['box_id'] ?? null;
if (!$box_id) {
    die('Box ID not specified.');
}

// Fetch the box details to display in the header
$stmt = $pdo->prepare("SELECT box_identifier FROM boxes WHERE box_id = ? AND user_id = ?");
$stmt->execute([$box_id, $_SESSION['user_id']]);
$box = $stmt->fetch();

if (!$box) {
    die('Box not found or you do not have permission.');
}

$page_title = 'Add Item to Box: ' . htmlspecialchars($box['box_identifier']);
include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Add New Item to <span class="text-primary"><?= htmlspecialchars($box['box_identifier']) ?></span></h2>
    <a href="../boxes/view.php?box_id=<?= $box_id ?>" class="btn btn-outline-secondary">&larr; Back to Box</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">New Item Details</h5>
    </div>
    <div class="card-body">
        <form id="add-item-form" action="../../handlers/items/add_item.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="box_id" value="<?= $box_id ?>">
            <input type="hidden" name="photo_data_url" id="photo_data_url">

            <div class="mb-3">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" name="item_name" id="item_name" class="form-control" required placeholder="e.g., Winter Jacket">
            </div>

            <div class="mb-3">
                <label class="form-label">Photo (Optional)</label>
                <div class="input-group">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#cameraModal">
                        Take Photo
                    </button>
                    <input type="file" name="photo_upload" id="photo_upload" class="form-control" accept="image/*">
                </div>
                <div class="form-text">You can either take a new photo or upload an existing file.</div>
            </div>

            <div id="photo-preview-container" class="mb-3" style="display: none;">
                <label class="form-label">Photo Preview:</label>
                <img id="photo-preview" src="#" alt="Photo Preview" class="img-fluid rounded" style="max-height: 200px;">
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Add Item to Box</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cameraModalLabel">Take a Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <video id="camera-stream" width="100%" autoplay playsinline></video>
                <canvas id="canvas" class="d-none"></canvas>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="capture-btn" class="btn btn-primary">Capture Photo</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cameraModal = new bootstrap.Modal(document.getElementById('cameraModal'));
    const cameraStream = document.getElementById('camera-stream');
    const captureBtn = document.getElementById('capture-btn');
    const canvas = document.getElementById('canvas');
    const photoDataUrlInput = document.getElementById('photo_data_url');
    const photoUploadInput = document.getElementById('photo_upload');
    const previewContainer = document.getElementById('photo-preview-container');
    const previewImage = document.getElementById('photo-preview');
    let stream;

    document.getElementById('cameraModal').addEventListener('shown.bs.modal', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            cameraStream.srcObject = stream;
        } catch (err) {
            console.error("Error accessing camera: ", err);
            alert('Could not access the camera. Please ensure you have given permission.');
            cameraModal.hide();
        }
    });

    document.getElementById('cameraModal').addEventListener('hidden.bs.modal', () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });

    captureBtn.addEventListener('click', () => {
        canvas.width = cameraStream.videoWidth;
        canvas.height = cameraStream.videoHeight;
        const context = canvas.getContext('2d');
        context.drawImage(cameraStream, 0, 0, canvas.width, canvas.height);
        
        const dataUrl = canvas.toDataURL('image/jpeg');
        photoDataUrlInput.value = dataUrl;

        previewImage.src = dataUrl;
        previewContainer.style.display = 'block';
        
        // **Crucial Change**: Disable the file input to prevent dual submission
        photoUploadInput.value = ''; 
        photoUploadInput.disabled = true;
        
        cameraModal.hide();
    });

    photoUploadInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            // Re-enable it if user chooses a file
            photoUploadInput.disabled = false;
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                previewContainer.style.display = 'block';
                photoDataUrlInput.value = ''; 
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>