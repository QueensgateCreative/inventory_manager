<?php
require_once '../config.php';
require_login();

$stmt = $pdo->prepare("SELECT * FROM boxes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$boxes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-3">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Your Boxes</h1>
        <a href="auth/logout.php" class="btn btn-danger rounded-pill">Logout</a>
    </div>

    <div class="mb-4 text-center text-md-start">
        <a href="boxes/add_box_form.php" class="btn btn-success btn-lg rounded-pill px-4 shadow-sm">
            + Add New Box
        </a>
    </div>

    <?php if (empty($boxes)): ?>
        <p class="text-muted text-center">No boxes added yet. Click "Add New Box" to get started!</p>
    <?php else: ?>
        <div class="list-group shadow-sm rounded-3 overflow-hidden">
            <?php foreach ($boxes as $box): ?>
                <div class="list-group-item list-group-item-action d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center py-3">
                    <div class="mb-2 mb-md-0">
                        <a href="boxes/view.php?box_id=<?= $box['box_id'] ?>" class="text-decoration-none text-dark">
                            <h5 class="mb-1 text-primary"><?= htmlspecialchars($box['box_identifier']) ?></h5>
                            <?php if (!empty($box['box_label'])): ?>
                                <p class="mb-0 text-muted"><small><?= htmlspecialchars($box['box_label']) ?></small></p>
                            <?php endif; ?>
                        </a>
                        <small class="text-muted d-block d-md-inline-block mt-1 mt-md-0">Added: <?= date('M d, Y', strtotime($box['created_at'])) ?></small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="boxes/view.php?box_id=<?= $box['box_id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">View Items</a>
                        <a href="../handlers/boxes/delete_box.php?box_id=<?= $box['box_id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Are you sure you want to delete this box and ALL its items? This action cannot be undone.');">Delete Box</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
