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

// Handle form submission to update the product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $item_name = trim($_POST['item_name']);
    $sku = trim($_POST['sku']);
    $quantity = (int) $_POST['quantity'];
    $rackzone = trim($_POST['rackzone']);
    $racknumber = trim($_POST['racknumber']);
    $image = $_FILES['image']['name'];

    // Validate input
    if (empty($item_name) || empty($sku) || empty($quantity) || empty($rackzone) || empty($racknumber)) {
        $_SESSION['error_message'] = "All fields are required.";
        header('Location: edit_product.php?id=' . $product_id);
        exit;
    }

    // Process image upload if a new image is provided
    if (!empty($image)) {
        $uploads_dir = 'uploads/';
        $image_path = $uploads_dir . basename($image);

        // Ensure the uploads directory exists and is writable
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0777, true); // Create the directory if it doesn't exist
        }

        // Check if the file is an image and upload it
        $image_info = getimagesize($_FILES['image']['tmp_name']);
        if ($image_info === false) {
            $_SESSION['error_message'] = "Uploaded file is not a valid image.";
            header('Location: edit_product.php?id=' . $product_id);
            exit;
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $_SESSION['error_message'] = "Failed to upload the image.";
            header('Location: edit_product.php?id=' . $product_id);
            exit;
        }

        $image_path = 'uploads/' . basename($image);
    } else {
        // Use the existing image if no new image is uploaded
        $image_path = $product['image'];
    }

    // Update the product in the database
    $stmt = $pdo->prepare('UPDATE warehouses SET item_name = ?, sku = ?, quantity = ?, rackzone = ?, racknumber = ?, image = ? WHERE id = ?');
    if ($stmt->execute([$item_name, $sku, $quantity, $rackzone, $racknumber, $image_path, $product_id])) {
        $_SESSION['success_message'] = "Product updated successfully!";
        header('Location: dashboard.php');
        exit;
    } else {
        $_SESSION['error_message'] = "Failed to update the product. Please try again.";
        header('Location: edit_product.php?id=' . $product_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - WareTrack Pro</title>
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
            <h1 class="display-4">Edit Product</h1>
            <p class="lead text-muted">Update product information</p>
        </div>

        <!-- Display Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success animate-fade-in">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger animate-fade-in">
                <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error_message']); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Edit Product Form -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <!-- Current Product Image Preview -->
                        <div class="text-center mb-4">
                            <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                                <div class="current-image-container">
                                    <img src="<?= htmlspecialchars($product['image']); ?>" 
                                         alt="Current Product Image" 
                                         class="current-product-image">
                                </div>
                                <p class="text-muted mt-2">Current Image</p>
                            <?php endif; ?>
                        </div>

                        <form method="POST" action="edit_product.php?id=<?= $product['id']; ?>" 
                              enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Item Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-box-seam"></i>
                                        </span>
                                        <input type="text" class="form-control" name="item_name" 
                                               value="<?= htmlspecialchars($product['item_name']); ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SKU</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-upc-scan"></i>
                                        </span>
                                        <input type="text" class="form-control" name="sku" 
                                               value="<?= htmlspecialchars($product['sku']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Quantity</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-123"></i>
                                        </span>
                                        <input type="number" class="form-control" name="quantity" 
                                               value="<?= $product['quantity']; ?>" min="0" required>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Rack Zone</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-grid"></i>
                                        </span>
                                        <select class="form-select" name="rackzone" required>
                                            <option value="Zone A" <?= $product['rackzone'] == 'Zone A' ? 'selected' : ''; ?>>Zone A</option>
                                            <option value="Zone B" <?= $product['rackzone'] == 'Zone B' ? 'selected' : ''; ?>>Zone B</option>
                                            <option value="Zone C" <?= $product['rackzone'] == 'Zone C' ? 'selected' : ''; ?>>Zone C</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Rack Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-hash"></i>
                                        </span>
                                        <input type="text" class="form-control" name="racknumber" 
                                               value="<?= htmlspecialchars($product['racknumber']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Update Image</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-image"></i>
                                    </span>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                </div>
                                <small class="text-muted">Leave empty to keep current image. Supported formats: JPG, PNG, GIF. Max size: 2MB</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="update_product" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Update Product
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Validation Script -->
    <script>
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
