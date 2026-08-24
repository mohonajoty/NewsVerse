<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// If user is already approved, redirect to dashboard
if ($_SESSION['approved'] == 1) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pending Approval - Joty News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark text-center">
            <h4><i class="fas fa-clock"></i> Account Pending Approval</h4>
        </div>
        <div class="card-body text-center">
            <div class="display-1 text-warning mb-4">
                <i class="fas fa-user-check"></i>
            </div>
            <h5>Hello, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h5>
            <p class="lead">Your account is waiting for admin approval.</p>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                You will be able to access all features once an administrator approves your account.
            </div>
            <p class="text-muted">Please check back later or contact the administrator.</p>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>
</body>
</html>