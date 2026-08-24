<?php
// Database configuration
$host = 'localhost';
$dbname = 'newsverse';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function isWriter() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'writer';
}

function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'user';
}

function isApproved() {
    return isset($_SESSION['approved']) && $_SESSION['approved'] == 1;
}

function getCurrentUser() {
    if(isset($_SESSION['user_id'])) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}
?>