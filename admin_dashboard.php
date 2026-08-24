<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get admin details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

// ============ HANDLE ACTIONS ============

// Handle User Approval
if (isset($_GET['approve_user'])) {
    $user_id = (int)$_GET['approve_user'];
    try {
        $stmt = $pdo->prepare("UPDATE users SET approved = 1, status = 'active' WHERE id = ? AND role = 'user'");
        $stmt->execute([$user_id]);
        $msg = "User approved successfully!";
        
        // Create notification for user
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, 'user_approval', 'Your account has been approved! You can now login.', 'login.php')");
        $stmt->execute([$user_id]);
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
    header("Location: admin_dashboard.php" . ($msg ? "?msg=" . urlencode($msg) : ""));
    exit();
}

// Handle User Rejection/Delete
if (isset($_GET['reject_user'])) {
    $user_id = (int)$_GET['reject_user'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user' AND approved = 0");
        $stmt->execute([$user_id]);
        $msg = "User rejected and removed!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
    header("Location: admin_dashboard.php" . ($msg ? "?msg=" . urlencode($msg) : ""));
    exit();
}

// Handle User Delete (for approved users)
if (isset($_GET['delete_user'])) {
    $user_id = (int)$_GET['delete_user'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        $stmt->execute([$user_id]);
        $msg = "User deleted successfully!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
    header("Location: admin_dashboard.php" . ($msg ? "?msg=" . urlencode($msg) : ""));
    exit();
}

// Handle User Suspend
if (isset($_GET['suspend_user'])) {
    $user_id = (int)$_GET['suspend_user'];
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ? AND role = 'user'");
        $stmt->execute([$user_id]);
        $msg = "User suspended successfully!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
    header("Location: admin_dashboard.php" . ($msg ? "?msg=" . urlencode($msg) : ""));
    exit();
}

// Handle User Activate
if (isset($_GET['activate_user'])) {
    $user_id = (int)$_GET['activate_user'];
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role = 'user'");
        $stmt->execute([$user_id]);
        $msg = "User activated successfully!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
    header("Location: admin_dashboard.php" . ($msg ? "?msg=" . urlencode($msg) : ""));
    exit();
}

// Handle Post Approval
if (isset($_GET['approve_post'])) {
    $post_id = (int)$_GET['approve_post'];
    try {
        $stmt = $pdo->prepare("UPDATE posts SET approved = 1, approved_by = ?, approved_at = NOW(), status = 'published' WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $post_id]);
        $msg = "Post approved successfully!";
        
        // Get the user who submitted the post
        $stmt = $pdo->prepare("SELECT submitted_by FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();
        
        if($post && $post['submitted_by']) {
            // Create notification for user
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, 'post_approval', 'Your post has been approved and published!', 'view_post.php?id=" . $post_id . "')");
            $stmt->execute([$post['submitted_by']]);
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
    header("Location: admin_dashboard.php" . ($msg ? "?msg=" . urlencode($msg) : ""));
    exit();
}

// Handle Post Rejection
if (isset($_GET['reject_post'])) {
    $post_id = (int)$_GET['reject_post'];
    try {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND approved = 0");
        $stmt->execute([$post_id]);
        $msg = "Post rejected and removed!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
    header("Location: admin_dashboard.php" . ($msg ? "?msg=" . urlencode($msg) : ""));
    exit();
}

// Handle Post Delete
if (isset($_GET['delete_post'])) {
    $post_id = (int)$_GET['delete_post'];
    try {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $msg = "Post deleted successfully!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
    header("Location: admin_dashboard.php" . ($msg ? "?msg=" . urlencode($msg) : ""));
    exit();
}

// Handle Comment Approval
if (isset($_GET['approve_comment'])) {
    $comment_id = (int)$_GET['approve_comment'];
    try {
        $stmt = $pdo->prepare("UPDATE comments SET status = 'approved' WHERE id = ?");
        $stmt->execute([$comment_id]);
        $msg = "Comment approved successfully!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
    header("Location: admin_dashboard.php" . ($msg ? "?msg=" . urlencode($msg) : ""));
    exit();
}

// Handle Comment Rejection
if (isset($_GET['reject_comment'])) {
    $comment_id = (int)$_GET['reject_comment'];
    try {
        $stmt = $pdo->prepare("UPDATE comments SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$comment_id]);
        $msg = "Comment rejected!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
    header("Location: admin_dashboard.php" . ($msg ? "?msg=" . urlencode($msg) : ""));
    exit();
}

// Handle Comment Delete
if (isset($_GET['delete_comment'])) {
    $comment_id = (int)$_GET['delete_comment'];
    try {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        $msg = "Comment deleted successfully!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
    header("Location: admin_dashboard.php" . ($msg ? "?msg=" . urlencode($msg) : ""));
    exit();
}

// ============ FETCH DATA ============

// Get statistics
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$pending_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND approved = 0")->fetchColumn();
$total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$pending_posts = $pdo->query("SELECT COUNT(*) FROM posts WHERE approved = 0")->fetchColumn();
$total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$pending_comments = $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn();
$total_admins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$total_views = $pdo->query("SELECT SUM(view_count) FROM posts")->fetchColumn() ?: 0;

// Get pending users
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'user' AND approved = 0 ORDER BY created_at DESC");
$pending_users_list = $stmt->fetchAll();

// Get pending posts with author info
$stmt = $pdo->query("
    SELECT p.*, u.username as submitted_by_username, u.full_name as submitted_by_name 
    FROM posts p 
    LEFT JOIN users u ON p.submitted_by = u.id 
    WHERE p.approved = 0 
    ORDER BY p.submission_date DESC
");
$pending_posts_list = $stmt->fetchAll();

// Get pending comments with post info
$stmt = $pdo->query("
    SELECT c.*, p.title as post_title, p.id as post_id 
    FROM comments c 
    LEFT JOIN posts p ON c.post_id = p.id 
    WHERE c.status = 'pending' 
    ORDER BY c.created_at DESC
");
$pending_comments_list = $stmt->fetchAll();

// Get recent posts (approved)
$stmt = $pdo->query("SELECT * FROM posts WHERE approved = 1 ORDER BY created_at DESC LIMIT 10");
$recent_posts = $stmt->fetchAll();

// Get all users
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC");
$all_users = $stmt->fetchAll();

// Get recent comments (approved)
$stmt = $pdo->query("
    SELECT c.*, p.title as post_title 
    FROM comments c 
    LEFT JOIN posts p ON c.post_id = p.id 
    WHERE c.status = 'approved' 
    ORDER BY c.created_at DESC 
    LIMIT 10
");
$recent_comments = $stmt->fetchAll();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$error = isset($error) ? $error : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard - Joty News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Hind Siliguri', sans-serif; }
        body { background: #f0f2f5; }
        
        .stat-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .stat-card .card-body {
            padding: 1.5rem;
        }
        .stat-card .stat-icon {
            font-size: 2.5rem;
            opacity: 0.3;
        }
        .stat-card .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
        }
        .stat-card .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .stat-card.bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card.bg-gradient-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        .stat-card.bg-gradient-warning {
            background: linear-gradient(135deg, #f2994a 0%, #f2c94a 100%);
            color: white;
        }
        .stat-card.bg-gradient-danger {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            color: white;
        }
        .stat-card.bg-gradient-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        .stat-card.bg-gradient-dark {
            background: linear-gradient(135deg, #2d3436 0%, #000000 100%);
            color: white;
        }

        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px 8px 0 0;
        }
        .nav-tabs .nav-link:hover {
            background: #e9ecef;
            border: none;
        }
        .nav-tabs .nav-link.active {
            color: #007bff;
            background: white;
            border-bottom: 3px solid #007bff;
        }
        .nav-tabs .nav-link .badge {
            margin-left: 5px;
        }

        .table th {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table td {
            vertical-align: middle;
        }

        .pending-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .action-btn {
            margin: 0 2px;
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        .sidebar-menu .nav-link {
            color: #333;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            margin-bottom: 2px;
            transition: all 0.3s;
        }
        .sidebar-menu .nav-link:hover,
        .sidebar-menu .nav-link.active {
            background: #007bff;
            color: white;
        }
        .sidebar-menu .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .stat-card .stat-number {
                font-size: 1.5rem;
            }
            .stat-card .stat-icon {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>

<!-- ============ TOP NAVBAR ============ -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="admin_dashboard.php">
            <i class="fas fa-newspaper me-2"></i> Joty Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-shield me-1"></i> <?= htmlspecialchars($_SESSION['full_name']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="index.php" target="_blank" class="btn btn-outline-light btn-sm ms-2">
                        <i class="fas fa-eye"></i> View Site
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-danger btn-sm ms-2">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ============ MAIN CONTENT ============ -->
<div class="container mt-4">

    <!-- Alert Messages -->
    <?php if($msg): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ============ STATISTICS CARDS ============ -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card stat-card bg-gradient-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Users</div>
                            <div class="stat-number"><?= number_format($total_users) ?></div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-warning"><i class="fas fa-clock"></i> <?= $pending_users ?> pending</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card stat-card bg-gradient-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Posts</div>
                            <div class="stat-number"><?= number_format($total_posts) ?></div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-newspaper"></i></div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-warning"><i class="fas fa-clock"></i> <?= $pending_posts ?> pending</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card stat-card bg-gradient-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Comments</div>
                            <div class="stat-number"><?= number_format($total_comments) ?></div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-comments"></i></div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-warning"><i class="fas fa-clock"></i> <?= $pending_comments ?> pending</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card stat-card bg-gradient-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Views</div>
                            <div class="stat-number"><?= number_format($total_views) ?></div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-eye"></i></div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-light"><i class="fas fa-user-shield"></i> <?= $total_admins ?> admins</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ QUICK ACTIONS ============ -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="fas fa-bolt text-warning"></i> Quick Actions</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="admin_create.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Create Post
                        </a>
                        <a href="#pendingPosts" class="btn btn-warning">
                            <i class="fas fa-clock"></i> Pending Posts <?php if($pending_posts > 0): ?><span class="badge bg-danger"><?= $pending_posts ?></span><?php endif; ?>
                        </a>
                        <a href="#pendingUsers" class="btn btn-info text-white">
                            <i class="fas fa-users"></i> Pending Users <?php if($pending_users > 0): ?><span class="badge bg-danger"><?= $pending_users ?></span><?php endif; ?>
                        </a>
                        <a href="#pendingComments" class="btn btn-secondary">
                            <i class="fas fa-comments"></i> Pending Comments <?php if($pending_comments > 0): ?><span class="badge bg-danger"><?= $pending_comments ?></span><?php endif; ?>
                        </a>
                        <a href="manage_posts.php" class="btn btn-success">
                            <i class="fas fa-list"></i> All Posts
                        </a>
                        <a href="#allUsers" class="btn btn-dark">
                            <i class="fas fa-users-cog"></i> Manage Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ PENDING APPROVALS TABS ============ -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="fw-bold mb-0"><i class="fas fa-tasks text-primary"></i> Pending Approvals</h5>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs" id="approvalTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#pendingUsers" type="button" role="tab">
                        <i class="fas fa-users"></i> Users
                        <?php if($pending_users > 0): ?>
                            <span class="badge bg-danger pending-badge"><?= $pending_users ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="posts-tab" data-bs-toggle="tab" data-bs-target="#pendingPosts" type="button" role="tab">
                        <i class="fas fa-file-alt"></i> Posts
                        <?php if($pending_posts > 0): ?>
                            <span class="badge bg-danger pending-badge"><?= $pending_posts ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="comments-tab" data-bs-toggle="tab" data-bs-target="#pendingComments" type="button" role="tab">
                        <i class="fas fa-comments"></i> Comments
                        <?php if($pending_comments > 0): ?>
                            <span class="badge bg-danger pending-badge"><?= $pending_comments ?></span>
                        <?php endif; ?>
                    </button>
                </li>
            </ul>
            
            <div class="tab-content mt-3">
                <!-- ======= PENDING USERS TAB ======= -->
                <div class="tab-pane fade show active" id="pendingUsers" role="tabpanel">
                    <?php if(count($pending_users_list) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($pending_users_list as $user): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= date('M d, Y h:i A', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <a href="?approve_user=<?= $user['id'] ?>" class="btn btn-success btn-sm action-btn" 
                                               onclick="return confirm('Approve this user?')">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                            <a href="?reject_user=<?= $user['id'] ?>" class="btn btn-danger btn-sm action-btn" 
                                               onclick="return confirm('Reject and remove this user?')">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                            <p class="text-muted mb-0">No pending user registrations.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- ======= PENDING POSTS TAB ======= -->
                <div class="tab-pane fade" id="pendingPosts" role="tabpanel">
                    <?php if(count($pending_posts_list) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Author</th>
                                        <th>Submitted By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($pending_posts_list as $post): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(mb_strimwidth($post['title'], 0, 30, '...')) ?></td>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($post['category']) ?></span></td>
                                        <td><?= htmlspecialchars($post['author_name']) ?></td>
                                        <td><?= htmlspecialchars($post['submitted_by_username'] ?? 'Unknown') ?></td>
                                        <td><?= date('M d, Y', strtotime($post['submission_date'])) ?></td>
                                        <td>
                                            <a href="preview_post.php?id=<?= $post['id'] ?>" class="btn btn-info btn-sm action-btn" target="_blank">
                                                <i class="fas fa-eye"></i> Preview
                                            </a>
                                            <a href="?approve_post=<?= $post['id'] ?>" class="btn btn-success btn-sm action-btn" 
                                               onclick="return confirm('Approve this post?')">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                            <a href="?reject_post=<?= $post['id'] ?>" class="btn btn-danger btn-sm action-btn" 
                                               onclick="return confirm('Reject and remove this post?')">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                            <p class="text-muted mb-0">No pending post submissions.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- ======= PENDING COMMENTS TAB ======= -->
                <div class="tab-pane fade" id="pendingComments" role="tabpanel">
                    <?php if(count($pending_comments_list) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Comment</th>
                                        <th>Post</th>
                                        <th>Commenter</th>
                                        <th>Email</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($pending_comments_list as $comment): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(mb_strimwidth($comment['comment_text'], 0, 50, '...')) ?></td>
                                        <td><?= htmlspecialchars(mb_strimwidth($comment['post_title'] ?? 'Deleted Post', 0, 25, '...')) ?></td>
                                        <td><?= htmlspecialchars($comment['commenter_name']) ?></td>
                                        <td><?= htmlspecialchars($comment['commenter_email']) ?></td>
                                        <td><?= date('M d, Y', strtotime($comment['created_at'])) ?></td>
                                        <td>
                                            <a href="?approve_comment=<?= $comment['id'] ?>" class="btn btn-success btn-sm action-btn" 
                                               onclick="return confirm('Approve this comment?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="?reject_comment=<?= $comment['id'] ?>" class="btn btn-warning btn-sm action-btn" 
                                               onclick="return confirm('Reject this comment?')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                            <a href="?delete_comment=<?= $comment['id'] ?>" class="btn btn-danger btn-sm action-btn" 
                                               onclick="return confirm('Delete this comment?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                            <p class="text-muted mb-0">No pending comments.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ ALL USERS ============ -->
    <div class="card shadow-sm border-0 mb-4" id="allUsers">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="fas fa-users text-primary"></i> All Users</h5>
            <span class="badge bg-secondary"><?= count($all_users) ?> users</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($all_users) > 0): ?>
                            <?php foreach($all_users as $user): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['approved'] ? ($user['status'] == 'active' ? 'success' : 'warning') : 'secondary' ?>">
                                        <?= $user['approved'] ? ucfirst($user['status']) : 'Pending' ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td><?= $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never' ?></td>
                                <td>
                                    <?php if(!$user['approved']): ?>
                                        <a href="?approve_user=<?= $user['id'] ?>" class="btn btn-success btn-sm action-btn" 
                                           onclick="return confirm('Approve this user?')">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php else: ?>
                                        <?php if($user['status'] == 'active'): ?>
                                            <a href="?suspend_user=<?= $user['id'] ?>" class="btn btn-warning btn-sm action-btn" 
                                               onclick="return confirm('Suspend this user?')">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="?activate_user=<?= $user['id'] ?>" class="btn btn-success btn-sm action-btn" 
                                               onclick="return confirm('Activate this user?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <a href="?delete_user=<?= $user['id'] ?>" class="btn btn-danger btn-sm action-btn" 
                                       onclick="return confirm('Delete this user? This action cannot be undone!')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============ RECENT POSTS ============ -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="fas fa-newspaper text-success"></i> Recent Posts</h5>
            <a href="manage_posts.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Views</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($recent_posts) > 0): ?>
                            <?php foreach($recent_posts as $post): ?>
                            <tr>
                                <td><?= htmlspecialchars(mb_strimwidth($post['title'], 0, 40, '...')) ?></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($post['category']) ?></span></td>
                                <td><?= htmlspecialchars($post['author_name']) ?></td>
                                <td><?= number_format($post['view_count'] ?? 0) ?></td>
                                <td><?= date('M d, Y', strtotime($post['created_at'])) ?></td>
                                <td>
                                    <a href="view_post.php?id=<?= $post['id'] ?>" class="btn btn-info btn-sm action-btn" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="admin_edit.php?id=<?= $post['id'] ?>" class="btn btn-warning btn-sm action-btn">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete_post=<?= $post['id'] ?>" class="btn btn-danger btn-sm action-btn" 
                                       onclick="return confirm('Delete this post?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No posts found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============ RECENT COMMENTS ============ -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="fas fa-comments text-info"></i> Recent Comments</h5>
            <a href="admin_comments.php" class="btn btn-sm btn-outline-primary">Manage All</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Comment</th>
                            <th>Post</th>
                            <th>Commenter</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($recent_comments) > 0): ?>
                            <?php foreach($recent_comments as $comment): ?>
                            <tr>
                                <td><?= htmlspecialchars(mb_strimwidth($comment['comment_text'], 0, 40, '...')) ?></td>
                                <td><?= htmlspecialchars(mb_strimwidth($comment['post_title'] ?? 'Deleted', 0, 25, '...')) ?></td>
                                <td><?= htmlspecialchars($comment['commenter_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $comment['status'] == 'approved' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($comment['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($comment['created_at'])) ?></td>
                                <td>
                                    <a href="?delete_comment=<?= $comment['id'] ?>" class="btn btn-danger btn-sm action-btn" 
                                       onclick="return confirm('Delete this comment?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No comments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ============ FOOTER ============ -->
<footer class="mt-5 py-3 bg-dark text-white text-center">
    <div class="container">
        <small>&copy; <?= date('Y') ?> Joty News Admin Panel. All rights reserved.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>