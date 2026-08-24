<?php
session_start();
include 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$post_id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Preview Post - NewsVerse Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; }
        .preview-container {
            max-width: 800px;
            margin: 40px auto;
        }
    </style>
</head>
<body>
<div class="preview-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-eye text-primary"></i> Preview Post</h4>
        <div>
            <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <a href="admin_dashboard.php?approve_post=<?= $post['id'] ?>" class="btn btn-success btn-sm" 
               onclick="return confirm('Approve this post?')">
                <i class="fas fa-check"></i> Approve
            </a>
            <a href="admin_dashboard.php?reject_post=<?= $post['id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Reject this post?')">
                <i class="fas fa-times"></i> Reject
            </a>
        </div>
    </div>
    
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if($post['main_image']): ?>
                <img src="<?= htmlspecialchars($post['main_image']) ?>" class="img-fluid w-100 rounded mb-3" style="max-height: 400px; object-fit: cover;" alt="Main image">
            <?php endif; ?>
            
            <div class="mb-2">
                <span class="badge bg-danger"><?= htmlspecialchars($post['category']) ?></span>
                <?php if($post['is_latest']): ?>
                    <span class="badge bg-warning text-dark">Latest</span>
                <?php endif; ?>
                <span class="badge bg-secondary">Pending Approval</span>
            </div>
            
            <h2 class="fw-bold mb-3"><?= htmlspecialchars($post['title']) ?></h2>
            
            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                <?php if($post['author_image']): ?>
                    <img src="<?= htmlspecialchars($post['author_image']) ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" class="me-2" alt="Author">
                <?php else: ?>
                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <strong><?= htmlspecialchars($post['author_name']) ?></strong>
                    <p class="text-muted small mb-0">
                        <i class="far fa-calendar-alt"></i> <?= date('F d, Y h:i A', strtotime($post['submission_date'] ?? $post['created_at'])) ?>
                    </p>
                </div>
            </div>
            
            <div class="content" style="line-height: 1.8; font-size: 1.1rem;">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>
            
            <div class="mt-4 p-3 bg-light rounded">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Submitted:</strong> <?= date('F d, Y h:i A', strtotime($post['submission_date'])) ?> | 
                    <strong>Status:</strong> <span class="badge bg-warning text-dark">Pending</span>
                </small>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>