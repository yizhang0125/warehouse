<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];
    
    // Delete the product from database
    $stmt = $pdo->prepare('DELETE FROM warehouses WHERE id = ?');
    if ($stmt->execute([$product_id])) {
        $_SESSION['success_message'] = "Product deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to delete product.";
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

header('Location: dashboard.php');
exit;
