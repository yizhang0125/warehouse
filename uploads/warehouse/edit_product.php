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
    <title>Edit Product</title>

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container my-5">
        <h1 class="text-center mb-4">Edit Product</h1>

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

        <!-- Form to update the product -->
        <form method="POST" action="edit_product.php?id=<?= $product['id']; ?>" enctype="multipart/form-data" class="card shadow-sm p-4">
            <div class="mb-3">
                <label for="item_name" class="form-label">Item Name:</label>
                <input type="text" name="item_name" value="<?= htmlspecialchars($product['item_name']); ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="sku" class="form-label">SKU:</label>
                <input type="text" name="sku" value="<?= htmlspecialchars($product['sku']); ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity:</label>
                <input type="number" name="quantity" value="<?= $product['quantity']; ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="rackzone" class="form-label">Rack Zone:</label>
                <select name="rackzone" class="form-select" required>
                    <option value="Zone A" <?= $product['rackzone'] == 'Zone A' ? 'selected' : ''; ?>>Zone A</option>
                    <option value="Zone B" <?= $product['rackzone'] == 'Zone B' ? 'selected' : ''; ?>>Zone B</option>
                    <option value="Zone C" <?= $product['rackzone'] == 'Zone C' ? 'selected' : ''; ?>>Zone C</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="racknumber" class="form-label">Rack Number:</label>
                <input type="text" name="racknumber" value="<?= htmlspecialchars($product['racknumber']); ?>" class="form-control" required>
            </div>

            <!-- Display existing image -->
            <div class="mb-3">
                <label for="current_image" class="form-label">Current Image:</label>
                <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                    <img src="<?= htmlspecialchars($product['image']); ?>" alt="Current Product Image" class="img-fluid mb-2" style="max-height: 150px;">
                <?php else: ?>
                    <p>No image available.</p>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Image (Leave blank to keep existing):</label>
                <input type="file" name="image" accept="image/*" class="form-control">
            </div>

            <button type="submit" name="update_product" class="btn btn-primary w-100">Update Product</button>
        </form>

        <br>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
