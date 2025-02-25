<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

// Handle Search
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
    
    // Fetch warehouse items with search functionality
    $stmt = $pdo->prepare('SELECT * FROM warehouses WHERE item_name LIKE ? OR sku LIKE ? ORDER BY created_at DESC');
    $stmt->execute(['%' . $search_query . '%', '%' . $search_query . '%']);
} else {
    $stmt = $pdo->query('SELECT * FROM warehouses ORDER BY created_at DESC');
}
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_products = count($items);
$total_quantity = array_sum(array_column($items, 'quantity'));
$low_stock_items = count(array_filter($items, function($item) {
    return $item['quantity'] <= 5;
}));

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
            <h1 class="display-4">Warehouse Dashboard</h1>
            <p class="lead text-muted">Manage your inventory efficiently</p>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stats-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Products</h6>
                            <h2 class="mb-0"><?= $total_products ?></h2>
                        </div>
                        <div class="icon">
                            <i class="bi bi-box"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Items</h6>
                            <h2 class="mb-0"><?= $total_quantity ?></h2>
                        </div>
                        <div class="icon">
                            <i class="bi bi-collection"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Low Stock Items</h6>
                            <h2 class="mb-0"><?= $low_stock_items ?></h2>
                        </div>
                        <div class="icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <form method="GET" class="search-wrapper" autocomplete="off" id="searchForm">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" 
                                   name="search" 
                                   id="searchInput"
                                   value="<?= htmlspecialchars($search_query); ?>" 
                                   class="form-control" 
                                   placeholder="Search products by name or SKU..."
                                   autofocus>
                            <div class="spinner-border text-primary search-spinner" role="status" style="display: none;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="add_product.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Add New Product
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Item Name</th>
                                <th>SKU</th>
                                <th>Quantity</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr class="animate-fade-in">
                                    <td>
                                        <?php if ($item['image']): ?>
                                            <img src="<?= htmlspecialchars($item['image']); ?>" 
                                                 alt="<?= htmlspecialchars($item['item_name']); ?>" 
                                                 class="product-image">
                                        <?php else: ?>
                                            <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['item_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">Added: <?= date('M d, Y', strtotime($item['created_at'])); ?></small>
                                    </td>
                                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($item['sku']); ?></span></td>
                                    <td>
                                        <?php
                                        $quantity = $item['quantity'];
                                        $badgeClass = $quantity > 10 ? 'bg-success' : ($quantity > 5 ? 'bg-warning' : 'bg-danger');
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= $quantity ?></span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <?= htmlspecialchars($item['rackzone']); ?> - 
                                            <?= htmlspecialchars($item['racknumber']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        // Determine stock status
                                        if ($quantity <= 0) {
                                            echo '<span class="badge bg-danger">Out of Stock</span>';
                                        } elseif ($quantity <= 5) {
                                            echo '<span class="badge bg-warning">Low Stock</span>';
                                        } else {
                                            echo '<span class="badge bg-success">In Stock</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="btn-group action-buttons">
                                            <a href="view_product.php?id=<?= $item['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit_product.php?id=<?= $item['id']; ?>" 
                                               class="btn btn-sm btn-outline-warning"
                                               title="Edit Product">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="delete_product.php" class="d-inline">
                                                <input type="hidden" name="product_id" value="<?= $item['id']; ?>">
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Are you sure you want to delete this product?');"
                                                        title="Delete Product">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // Add loading state to buttons
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            if (button) {
                button.classList.add('btn-loading');
                button.disabled = true;
            }
        });
    });

    // Live Search functionality
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const spinner = document.querySelector('.search-spinner');
    let timeout = null;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Show spinner
            spinner.style.display = 'block';
            
            // Clear the existing timeout
            clearTimeout(timeout);
            
            // Set a new timeout
            timeout = setTimeout(() => {
                // Get the search query
                const query = this.value.trim();
                
                // Create URL with search parameter
                const url = new URL(window.location.href);
                url.searchParams.set('search', query);
                
                // Fetch results
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        // Create a temporary element to parse the HTML
                        const temp = document.createElement('div');
                        temp.innerHTML = html;
                        
                        // Get the new table body
                        const newTableBody = temp.querySelector('.table tbody');
                        const currentTableBody = document.querySelector('.table tbody');
                        
                        // Update the table body with new results
                        if (newTableBody && currentTableBody) {
                            currentTableBody.innerHTML = newTableBody.innerHTML;
                        }
                        
                        // Update the URL without refreshing the page
                        window.history.pushState({}, '', url);
                        
                        // Hide spinner
                        spinner.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        spinner.style.display = 'none';
                    });
            }, 300); // Will search 300ms after user stops typing
        });
    }

    // Replace the existing delete form JavaScript with this:
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const productName = this.closest('tr').querySelector('strong').textContent;
            
            if (confirm(`Are you sure you want to delete "${productName}"?`)) {
                form.submit();
            }
        });
    });
    </script>
</body>
</html>
