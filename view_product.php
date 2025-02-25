<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

// Fetch the product ID from the URL
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Product ID is required.";
    header('Location: dashboard.php');
    exit;
}

$product_id = (int) $_GET['id'];

// Fetch the product details from the database
$stmt = $pdo->prepare('SELECT * FROM warehouses WHERE id = ?');
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error_message'] = "Product not found.";
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Product - WareTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-box-seam me-2"></i>WareTrack Pro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_product.php">
                            <i class="bi bi-plus-circle me-1"></i>Add Product
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="bi bi-graph-up me-1"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <!-- Page Header -->
        <div class="page-header text-center">
            <h1 class="display-4">Product Details</h1>
            <p class="lead text-muted">View complete product information</p>
            </div>

        <!-- Product Details Card -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card product-details-card">
                    <div class="card-body p-4">
                <div class="row">
                            <!-- Product Image -->
                            <div class="col-md-5 mb-4 mb-md-0">
                                <div class="product-image-container">
                                    <?php if ($product['image'] && file_exists($product['image'])): ?>
                                        <img src="<?= htmlspecialchars($product['image']); ?>" 
                                             alt="<?= htmlspecialchars($product['item_name']); ?>" 
                                             class="product-detail-image">
                        <?php else: ?>
                                        <div class="no-image-placeholder">
                                            <i class="bi bi-image text-muted"></i>
                                            <p>No image available</p>
                                        </div>
                        <?php endif; ?>
                    </div>
                            </div>

                            <!-- Product Information -->
                            <div class="col-md-7">
                                <h2 class="product-title mb-4">
                                    <?= htmlspecialchars($product['item_name']); ?>
                                </h2>

                                <div class="product-info-grid">
                                    <div class="info-item">
                                        <label class="info-label">
                                            <i class="bi bi-upc-scan text-primary me-2"></i>SKU
                                        </label>
                                        <p class="info-value"><?= htmlspecialchars($product['sku']); ?></p>
                                    </div>

                                    <div class="info-item">
                                        <label class="info-label">
                                            <i class="bi bi-boxes text-primary me-2"></i>Quantity
                                        </label>
                                        <p class="info-value">
                                            <?php
                                            $quantity = $product['quantity'];
                                            $badgeClass = $quantity > 10 ? 'bg-success' : ($quantity > 5 ? 'bg-warning' : 'bg-danger');
                                            $status = $quantity > 10 ? 'In Stock' : ($quantity > 5 ? 'Low Stock' : 'Critical Stock');
                                            ?>
                                            <span class="badge <?= $badgeClass ?> me-2"><?= $quantity ?></span>
                                            <span class="text-muted"><?= $status ?></span>
                                        </p>
                                    </div>

                                    <div class="info-item">
                                        <label class="info-label">
                                            <i class="bi bi-currency-dollar text-primary me-2"></i>Price
                                        </label>
                                        <p class="info-value">
                                            $<?= number_format($product['price'], 2); ?>
                                        </p>
                                    </div>

                                    <div class="info-item">
                                        <label class="info-label">
                                            <i class="bi bi-geo-alt text-primary me-2"></i>Location
                                        </label>
                                        <p class="info-value">
                                            Zone: <span class="badge bg-info"><?= htmlspecialchars($product['rackzone']); ?></span>
                                            Rack: <span class="badge bg-secondary"><?= htmlspecialchars($product['racknumber']); ?></span>
                                        </p>
                                    </div>

                                    <div class="info-item">
                                        <label class="info-label">
                                            <i class="bi bi-calendar-check text-primary me-2"></i>Added On
                                        </label>
                                        <p class="info-value">
                                            <?= date('F d, Y', strtotime($product['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-4 d-flex gap-2">
                                    <a href="edit_product.php?id=<?= $product['id']; ?>" 
                                       class="btn btn-warning">
                                        <i class="bi bi-pencil me-2"></i>Edit Product
                                    </a>
                                    <button type="button" 
                                            class="btn btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal">
                                        <i class="bi bi-trash me-2"></i>Delete Product
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="text-center mt-4">
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this product?</p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="delete_product.php" method="POST" class="d-inline">
                        <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                        <button type="submit" class="btn btn-danger">Delete Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
