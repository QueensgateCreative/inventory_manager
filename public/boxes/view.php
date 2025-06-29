<?php
require_once '../../config.php';
require_login();

// Retrieve box_id from GET request
$box_id = $_GET['box_id'] ?? null;
if (!$box_id) {
    die('Box not specified.');
}

// Fetch box details, ensuring it belongs to the logged-in user
$stmt = $pdo->prepare("SELECT * FROM boxes WHERE box_id = ? AND user_id = ?");
$stmt->execute([$box_id, $_SESSION['user_id']]);
$box = $stmt->fetch();
if (!$box) {
    die('Box not found or you do not have permission to view it.');
}

// Fetch all items within this box
$stmt = $pdo->prepare("SELECT * FROM items WHERE box_id = ? ORDER BY created_at DESC");
$stmt->execute([$box_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Box: <?= htmlspecialchars($box['box_identifier']) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-3">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-center text-md-start">Box: <?= htmlspecialchars($box['box_identifier']) ?> <br class="d-md-none"><small class="text-muted fs-5">- <?= htmlspecialchars($box['box_label']) ?></small></h2>
        <a href="../dashboard.php" class="btn btn-outline-secondary rounded-pill">&larr; Back to Dashboard</a>
    </div>

    <!-- Button to Add New Item -->
    <div class="mb-4 text-center text-md-start">
        <a href="../items/add_item_form.php?box_id=<?= $box_id ?>" class="btn btn-success btn-lg rounded-pill px-4 shadow-sm">
            + Add New Item
        </a>
    </div>

    <h4>Items in this box:</h4>
    <?php if (empty($items)): ?>
        <p class="text-muted">No items found in this box. Click "Add New Item" to add your first item!</p>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($items as $item): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                        <?php if ($item['photo_path']): ?>
                            <img src="<?= htmlspecialchars($item['photo_path']) ?>" class="card-img-top object-fit-cover" alt="Item Photo" style="height: 200px;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <span class="text-muted">No Photo</span>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-primary"><?= htmlspecialchars($item['item_name']) ?></h5>
                            <?php if ($item['barcode']): ?>
                                <p class="card-text mb-1"><small class="text-muted">Barcode: <?= htmlspecialchars($item['barcode']) ?></small></p>
                            <?php endif; ?>
                            <div class="mt-auto pt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill edit-item-btn"
                                        data-bs-toggle="modal" data-bs-target="#editItemModal"
                                        data-item-id="<?= $item['item_id'] ?>"
                                        data-item-name="<?= htmlspecialchars($item['item_name']) ?>"
                                        data-barcode="<?= htmlspecialchars($item['barcode']) ?>"
                                        data-photo-path="<?= htmlspecialchars($item['photo_path']) ?>">
                                    Edit Item
                                </button>
                                <a href="../../handlers/items/delete_item.php?item_id=<?= $item['item_id'] ?>&box_id=<?= $box_id ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Item Modal (Remains on view.php as it's for editing existing items) -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title" id="editItemModalLabel">Edit Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editItemForm" action="../../handlers/items/edit_item.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="item_id" id="edit_item_id">
                    <input type="hidden" name="box_id" value="<?= $box_id ?>">
                    <input type="hidden" name="current_photo_path" id="edit_current_photo_path">

                    <div class="mb-3">
                        <label for="edit_item_name" class="form-label">Item Name</label>
                        <input type="text" name="item_name" id="edit_item_name" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_barcode" class="form-label">Barcode or ID</label>
                        <input type="text" name="barcode" id="edit_barcode" class="form-control rounded-3">
                    </div>
                    <div class="mb-3">
                        <label for="edit_photo" class="form-label">New Photo (Optional)</label>
                        <input type="file" name="photo" id="edit_photo" class="form-control rounded-3" accept="image/*" capture="environment">
                        <small class="form-text text-muted">Upload a new photo to replace the existing one.</small>
                    </div>
                    <div class="mb-3" id="currentPhotoPreview" style="display: none;">
                        <label class="form-label">Current Photo:</label><br>
                        <img id="edit_photo_preview" src="" alt="Current Item Photo" class="img-thumbnail rounded-3" style="max-width: 150px; height: auto;">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="remove_photo" name="remove_photo" value="1">
                            <label class="form-check-label" for="remove_photo">
                                Remove Current Photo
                            </label>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // JavaScript to handle populating the modal when "Edit Item" button is clicked
    document.addEventListener('DOMContentLoaded', function() {
        var editItemModal = document.getElementById('editItemModal');
        editItemModal.addEventListener('show.bs.modal', function (event) {
            // Button that triggered the modal
            var button = event.relatedTarget;

            // Extract info from data-* attributes
            var itemId = button.getAttribute('data-item-id');
            var itemName = button.getAttribute('data-item-name');
            var barcode = button.getAttribute('data-barcode');
            var photoPath = button.getAttribute('data-photo-path');

            // Update the modal's content.
            var modalForm = editItemModal.querySelector('#editItemForm');
            modalForm.querySelector('#edit_item_id').value = itemId;
            modalForm.querySelector('#edit_item_name').value = itemName;
            modalForm.querySelector('#edit_barcode').value = barcode;
            modalForm.querySelector('#edit_current_photo_path').value = photoPath;

            var currentPhotoPreviewDiv = modalForm.querySelector('#currentPhotoPreview');
            var editPhotoPreviewImg = modalForm.querySelector('#edit_photo_preview');
            var removePhotoCheckbox = modalForm.querySelector('#remove_photo');

            // Reset checkbox state
            removePhotoCheckbox.checked = false;

            if (photoPath) {
                editPhotoPreviewImg.src = photoPath;
                currentPhotoPreviewDiv.style.display = 'block';
            } else {
                currentPhotoPreviewDiv.style.display = 'none';
                editPhotoPreviewImg.src = ''; // Clear src
            }
        });
    });
</script>
</body>
</html>
