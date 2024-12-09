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
    <title>View Product</title>

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container my-5">
        <h1 class="text-center mb-4">Product Details</h1>

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

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h4>Item Image:</h4>
                        <?php if ($product['image']): ?>
                            <img src="<?= htmlspecialchars($product['image']); ?>" alt="Product Image" class="img-fluid rounded">
                        <?php else: ?>
                            <p>No image available.</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <h4>Item Name:</h4>
                        <p><?= htmlspecialchars($product['item_name']); ?></p>

                        <h4>SKU:</h4>
                        <p><?= htmlspecialchars($product['sku']); ?></p>

                        <h4>Quantity:</h4>
                        <p><?= $product['quantity']; ?></p>

                        <h4>Rack Zone:</h4>
                        <p><?= htmlspecialchars($product['rackzone']); ?></p>

                        <h4>Rack Number:</h4>
                        <p><?= htmlspecialchars($product['racknumber']); ?></p>

                        <h4>Added On:</h4>
                        <p><?= $product['created_at']; ?></p>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
