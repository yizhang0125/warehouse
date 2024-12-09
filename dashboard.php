<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

// Initialize search query
$search_query = '';

// Check if the search form is submitted
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// Fetch warehouse items, optionally filtering by search query
if (!empty($search_query)) {
    $stmt = $pdo->prepare('SELECT * FROM warehouses WHERE item_name LIKE ? OR sku LIKE ?');
    $stmt->execute(['%' . $search_query . '%', '%' . $search_query . '%']);
} else {
    $stmt = $pdo->query('SELECT * FROM warehouses');
}

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Display success or error message if set
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);  // Clear the success message after displaying it
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);  // Clear the error message after displaying it
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Dashboard</title>
    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Style to ensure images fill the box and maintain aspect ratio */
        .product-image {
            width: 150px; /* Set the desired width */
            height: 150px; /* Set the desired height */
            object-fit: cover; /* Ensures the image fills the container without distortion */
        }
    </style>
</head>
<body class="bg-light">

    <div class="container my-5">
        <h1 class="text-center mb-4">Warehouse Dashboard</h1>

        <!-- Display success or error message if set -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between mb-4">
            <a href="logout.php" class="btn btn-danger">Logout</a>
            <a href="add_product.php" class="btn btn-primary">Add New Product</a>
        </div>

        <!-- Search form -->
        <form method="GET" class="d-flex mb-4">
            <input type="text" name="search" value="<?= htmlspecialchars($search_query); ?>" class="form-control me-2" placeholder="Search by item name or SKU" aria-label="Search">
            <button type="submit" class="btn btn-success">Search</button>
        </form>

        <h2>Product List</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Item Name</th>
                        <th>SKU</th>
                        <th>Quantity</th>
                        <th>Rack Zone</th>
                        <th>Rack Number</th>
                        <th>Added On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['image']): ?>
                                        <img src="<?= htmlspecialchars($item['image']); ?>" alt="Product Image" class="product-image">
                                    <?php else: ?>
                                        No image
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['item_name']); ?></td>
                                <td><?= htmlspecialchars($item['sku']); ?></td>
                                <td><?= $item['quantity']; ?></td>
                                <td><?= htmlspecialchars($item['rackzone']); ?></td>
                                <td><?= htmlspecialchars($item['racknumber']); ?></td>
                                <td><?= $item['created_at']; ?></td>
                                <td>
                                    <a href="view_product.php?id=<?= $item['id']; ?>" class="btn btn-info btn-sm">View</a>
                                    <a href="edit_product.php?id=<?= $item['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="delete_product.php?id=<?= $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap 5 JS (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
