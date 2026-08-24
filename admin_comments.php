<?php
session_start();
include 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle comment actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $comment_id = (int)$_GET['id'];
    
    try {
        if ($action == 'approve') {
            $stmt = $pdo->prepare("UPDATE comments SET status = 'approved' WHERE id = ?");
            $stmt->execute([$comment_id]);
            $msg = "Comment approved successfully";
        } elseif ($action == 'reject') {
            $stmt = $pdo->prepare("UPDATE comments SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$comment_id]);
            $msg = "Comment rejected";
        } elseif ($action == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
            $stmt->execute([$comment_id]);
            $msg = "Comment deleted successfully";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
    
    header("Location: admin_comments.php" . ($msg ? "?msg=" . urlencode($msg) : ""));
    exit();
}

// Get all comments with post info
$stmt = $pdo->query("
    SELECT c.*, p.title as post_title, p.id as post_id 
    FROM comments c 
    LEFT JOIN posts p ON c.post_id = p.id 
    ORDER BY c.created_at DESC
");
$comments = $stmt->fetchAll();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$error = isset($error) ? $error : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Comments - NewsVerse Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; }
        .comment-card {
            border-left: 4px solid #007bff;
            transition: all 0.3s;
        }
        .comment-card:hover {
            transform: translateX(5px);
        }
        .comment-card.pending {
            border-left-color: #ffc107;
        }
        .comment-card.approved {
            border-left-color: #28a745;
        }
        .comment-card.rejected {
            border-left-color: #dc3545;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="admin_dashboard.php">
            <i class="fas fa-newspaper me-2"></i> NewsVerse Admin
        </a>
        <div class="navbar-nav ms-auto">
            <a href="admin_dashboard.php" class="btn btn-light btn-sm me-2">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="logout.php" class="btn btn-danger btn-sm">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="fas fa-comments text-primary"></i> Manage Comments</h5>
            <span class="badge bg-secondary"><?= count($comments) ?> total</span>
        </div>
        <div class="card-body">
            <?php if($msg): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if(count($comments) > 0): ?>
                <div class="row">
                    <?php foreach($comments as $comment): ?>
                        <div class="col-12 mb-3">
                            <div class="comment-card <?= $comment['status'] ?> p-3 bg-white rounded shadow-sm">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <h6 class="fw-bold mb-0 me-2"><?= htmlspecialchars($comment['commenter_name']) ?></h6>
                                            <span class="badge bg-<?= $comment['status'] == 'approved' ? 'success' : ($comment['status'] == 'pending' ? 'warning' : 'danger') ?>">
                                                <?= ucfirst($comment['status']) ?>
                                            </span>
                                            <?php if($comment['user_id']): ?>
                                                <span class="badge bg-info">Member</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="mb-2"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></p>
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt"></i> <?= date('F d, Y h:i A', strtotime($comment['created_at'])) ?>
                                            | <i class="fas fa-newspaper"></i> 
                                            <a href="view_post.php?id=<?= $comment['post_id'] ?>" target="_blank">
                                                <?= htmlspecialchars($comment['post_title'] ?? 'Deleted Post') ?>
                                            </a>
                                            | <i class="fas fa-envelope"></i> <?= htmlspecialchars($comment['commenter_email']) ?>
                                        </small>
                                    </div>
                                    <div class="ms-3">
                                        <?php if($comment['status'] == 'pending'): ?>
                                            <a href="?action=approve&id=<?= $comment['id'] ?>" class="btn btn-success btn-sm" 
                                               onclick="return confirm('Approve this comment?')">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                            <a href="?action=reject&id=<?= $comment['id'] ?>" class="btn btn-warning btn-sm"
                                               onclick="return confirm('Reject this comment?')">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?= $comment['id'] ?>" class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this comment?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted">No comments found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>