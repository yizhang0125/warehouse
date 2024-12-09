<?php
session_start();
require 'db.php';

// If user is not logged in, redirect to login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Check if product ID is provided
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Delete the product from the database
    $stmt = $pdo->prepare('DELETE FROM warehouses WHERE id = ?');
    if ($stmt->execute([$id])) {
        $_SESSION['success_message'] = "Product deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to delete the product. Please try again.";
    }
}

header('Location: dashboard.php');
exit;
