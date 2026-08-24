<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to like posts']);
    exit();
}

// Get parameters
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = $_SESSION['user_id'];

// Check if post exists
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if(!$post) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit();
}

// Check if user already liked (using 'likes' table, not 'post_likes')
$stmt = $pdo->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);
$user_liked = $stmt->fetch() ? true : false;

$response = ['success' => false];

if($action == 'like' && !$user_liked) {
    // Add like
    $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
    $stmt->execute([$post_id, $user_id]);
    
    // Update likes count
    $stmt = $pdo->prepare("UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?");
    $stmt->execute([$post_id]);
    
    // Get new count
    $stmt = $pdo->prepare("SELECT likes_count FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $new_count = $stmt->fetchColumn();
    
    $response = [
        'success' => true,
        'action' => 'liked',
        'likes_count' => $new_count
    ];
} elseif($action == 'unlike' && $user_liked) {
    // Remove like
    $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    
    // Update likes count
    $stmt = $pdo->prepare("UPDATE posts SET likes_count = likes_count - 1 WHERE id = ?");
    $stmt->execute([$post_id]);
    
    // Get new count
    $stmt = $pdo->prepare("SELECT likes_count FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $new_count = $stmt->fetchColumn();
    
    $response = [
        'success' => true,
        'action' => 'unliked',
        'likes_count' => $new_count
    ];
} else {
    $response = [
        'success' => false,
        'message' => 'Invalid action or already ' . ($action == 'like' ? 'liked' : 'unliked')
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>