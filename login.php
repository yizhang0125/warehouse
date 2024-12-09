<?php
session_start();
require 'db.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Check for any success or error messages from session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);  // Clear the success message after displaying it
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate the input
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Username and Password are required.";
        header('Location: login.php');
        exit;
    } else {
        // Prepare the query to get the admin by username
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        // Check if admin exists and verify the password
        if ($admin && password_verify($password, $admin['password'])) {
            // Set session variables and success message
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['success_message'] = "Login successful!";
            header('Location: dashboard.php');
            exit;
        } else {
            // Invalid credentials
            $_SESSION['error_message'] = "Invalid username or password.";
            header('Location: login.php');
            exit;
        }
    }
}
?>

<!-- HTML for login page remains the same -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h1 class="text-center my-5">Admin Login</h1>

                <!-- Display success message if set -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Display error message if set -->
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <div class="text-center mt-4">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
