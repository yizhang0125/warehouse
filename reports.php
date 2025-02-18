<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

// Fetch statistics
$stmt = $pdo->query('SELECT 
    COUNT(*) as total_products,
    SUM(quantity) as total_stock,
    AVG(quantity) as avg_stock
FROM warehouses');
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get low stock items (quantity <= 5)
$stmt = $pdo->query('SELECT COUNT(*) as low_stock FROM warehouses WHERE quantity <= 5');
$low_stock = $stmt->fetch(PDO::FETCH_ASSOC);

// Get stock by zone
$stmt = $pdo->query('SELECT rackzone, COUNT(*) as count, SUM(quantity) as total_quantity 
                     FROM warehouses GROUP BY rackzone');
$zone_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent activities (last 10 products added)
$stmt = $pdo->query('SELECT * FROM warehouses ORDER BY created_at DESC LIMIT 10');
$recent_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - WareTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a class="nav-link active" href="reports.php">
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
            <h1 class="display-4">Inventory Reports</h1>
            <p class="lead text-muted">Comprehensive overview of your warehouse statistics</p>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Total Products</h6>
                                <h2 class="mb-0"><?= number_format($stats['total_products']) ?></h2>
                            </div>
                            <div class="stats-icon">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Total Stock</h6>
                                <h2 class="mb-0"><?= number_format($stats['total_stock']) ?></h2>
                            </div>
                            <div class="stats-icon">
                                <i class="bi bi-boxes"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Average Stock</h6>
                                <h2 class="mb-0"><?= number_format($stats['avg_stock'], 1) ?></h2>
                            </div>
                            <div class="stats-icon">
                                <i class="bi bi-calculator"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Low Stock Items</h6>
                                <h2 class="mb-0"><?= number_format($low_stock['low_stock']) ?></h2>
                            </div>
                            <div class="stats-icon">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Stock by Zone</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="zoneChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Stock Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="stockDistribution" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Activities</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Quantity</th>
                                <th>Location</th>
                                <th>Added On</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <?php if ($product['image'] && file_exists($product['image'])): ?>
                                                <img src="<?= htmlspecialchars($product['image']); ?>" 
                                                     alt="<?= htmlspecialchars($product['item_name']); ?>" 
                                                     class="product-thumbnail">
                                            <?php else: ?>
                                                <div class="product-thumbnail d-flex align-items-center justify-content-center bg-light">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <p class="product-name"><?= htmlspecialchars($product['item_name']); ?></p>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($product['sku']); ?></td>
                                    <td><?= $product['quantity']; ?></td>
                                    <td>
                                        <?= htmlspecialchars($product['rackzone']); ?> - 
                                        <?= htmlspecialchars($product['racknumber']); ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($product['created_at'])); ?></td>
                                    <td>
                                        <?php
                                        $quantity = $product['quantity'];
                                        if ($quantity <= 0) {
                                            echo '<span class="badge bg-danger">Out of Stock</span>';
                                        } elseif ($quantity <= 5) {
                                            echo '<span class="badge bg-warning">Low Stock</span>';
                                        } else {
                                            echo '<span class="badge bg-success">In Stock</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Prepare data for charts
    const zoneData = <?= json_encode($zone_stats) ?>;
    
    // Zone Chart
    new Chart(document.getElementById('zoneChart'), {
        type: 'bar',
        data: {
            labels: zoneData.map(item => item.rackzone),
            datasets: [{
                label: 'Products',
                data: zoneData.map(item => item.count),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }, {
                label: 'Total Quantity',
                data: zoneData.map(item => item.total_quantity),
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgb(255, 99, 132)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Stock Distribution
    new Chart(document.getElementById('stockDistribution'), {
        type: 'pie',
        data: {
            labels: ['Normal Stock', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [
                    <?= $stats['total_products'] - $low_stock['low_stock'] ?>,
                    <?= $low_stock['low_stock'] ?>,
                    0 // You might want to add a query for out of stock items
                ],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(255, 99, 132, 0.5)'
                ],
                borderColor: [
                    'rgb(75, 192, 192)',
                    'rgb(255, 206, 86)',
                    'rgb(255, 99, 132)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 