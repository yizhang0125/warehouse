<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

// Handle form submission to add a new product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $item_name = trim($_POST['item_name']);
    $sku = trim($_POST['sku']);
    $quantity = (int) $_POST['quantity'];
    $rackzone = trim($_POST['rackzone']);
    $racknumber = trim($_POST['racknumber']);
    $image = $_FILES['image']['name'];

    // Validate input
    if (empty($item_name) || empty($sku) || empty($quantity) || empty($rackzone) || empty($racknumber) || empty($image)) {
        $_SESSION['error_message'] = "All fields are required.";
        header('Location: add_product.php');
        exit;
    }

    // Process image upload
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
        header('Location: add_product.php');
        exit;
    }

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
        $_SESSION['error_message'] = "Failed to upload the image.";
        header('Location: add_product.php');
        exit;
    }

    // Insert new product into the database
    $stmt = $pdo->prepare('INSERT INTO warehouses (item_name, sku, quantity, rackzone, racknumber, image) VALUES (?, ?, ?, ?, ?, ?)');
    if ($stmt->execute([$item_name, $sku, $quantity, $rackzone, $racknumber, $image_path])) {
        $_SESSION['success_message'] = "Product added successfully!";
        header('Location: dashboard.php');
        exit;
    } else {
        $_SESSION['error_message'] = "Failed to add the product. Please try again.";
        header('Location: add_product.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1 class="text-center mb-4">Add New Product</h1>

        <!-- Display success or error message if set -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error_message']); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Form to add a new product -->
        <form method="POST" action="add_product.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="item_name">Item Name</label>
                <input type="text" class="form-control" name="item_name" required>
            </div>

            <div class="form-group">
                <label for="sku">SKU</label>
                <input type="text" class="form-control" name="sku" required>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" class="form-control" name="quantity" required>
            </div>

            <div class="form-group">
                <label for="rackzone">Rack Zone</label>
                <select class="form-select" name="rackzone" required>
                    <option value="Zone A">Zone A</option>
                    <option value="Zone B">Zone B</option>
                    <option value="Zone C">Zone C</option>
                </select>
            </div>

            <div class="form-group">
                <label for="racknumber">Rack Number</label>
                <input type="text" class="form-control" name="racknumber" required>
            </div>

            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" class="form-control" name="image" accept="image/*" required>
            </div>

            <button type="submit" name="add_product" class="btn btn-primary w-100 mt-4">Add Product</button>
        </form>

        <br>
        <a href="dashboard.php" class="btn btn-secondary w-100">Back to Dashboard</a>
    </div>

    <!-- Bootstrap 5 JS (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
