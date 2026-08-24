<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user is approved
if ($_SESSION['approved'] == 0) {
    header("Location: pending_approval.php");
    exit();
}

// Redirect admin to admin dashboard
if ($_SESSION['role'] == 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_post'])) {
    $category = trim($_POST['category']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $author_name = trim($_POST['author_name']);
    
    // Handle image uploads
    $main_image = '';
    $author_image = '';
    $target_dir = "uploads/";
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Upload main image
    if (!empty($_FILES["main_image"]["name"])) {
        $main_image = $target_dir . time() . "_" . basename($_FILES["main_image"]["name"]);
        move_uploaded_file($_FILES["main_image"]["tmp_name"], $main_image);
    }
    
    // Upload author image
    if (!empty($_FILES["author_image"]["name"])) {
        $author_image = $target_dir . time() . "_auth_" . basename($_FILES["author_image"]["name"]);
        move_uploaded_file($_FILES["author_image"]["tmp_name"], $author_image);
    }
    
    if (empty($category) || empty($title) || empty($content) || empty($author_name)) {
        $error = "All required fields must be filled!";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO posts (category, title, author_name, author_image, content, main_image, submitted_by, submission_date, approved, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0, 'pending')");
            $stmt->execute([$category, $title, $author_name, $author_image, $content, $main_image, $user_id]);
            
            $message = "Your post has been submitted for admin approval!";
            
            // Clear form
            $_POST = array();
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get user's submitted posts
$stmt = $pdo->prepare("SELECT * FROM posts WHERE submitted_by = ? ORDER BY submission_date DESC");
$stmt->execute([$user_id]);
$user_posts = $stmt->fetchAll();

// Count pending and approved posts
$pending_count = 0;
$approved_count = 0;
foreach($user_posts as $p) {
    if($p['approved'] == 0) $pending_count++;
    else $approved_count++;
}

// Get approved and published posts
$stmt = $pdo->prepare("SELECT * FROM posts WHERE approved = 1 AND status = 'published' ORDER BY created_at DESC LIMIT 10");
$stmt->execute();
$published_posts = $stmt->fetchAll();

// Get categories for sidebar
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");

// Get user's bookmarks
$stmt = $pdo->prepare("SELECT p.* FROM posts p INNER JOIN bookmarks b ON p.id = b.post_id WHERE b.user_id = ? AND p.approved = 1 ORDER BY b.created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$bookmarks = $stmt->fetchAll();

// Get reading history
$stmt = $pdo->prepare("SELECT p.* FROM posts p INNER JOIN reading_history r ON p.id = r.post_id WHERE r.user_id = ? ORDER BY r.read_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$reading_history = $stmt->fetchAll();

// Get user's comments
$stmt = $pdo->prepare("SELECT c.*, p.title as post_title FROM comments c LEFT JOIN posts p ON c.post_id = p.id WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$user_comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Dashboard - NewsVerse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f0f2f5; }
        
        /* ============ TOP NAVBAR ============ */
        .top-navbar {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            padding: 12px 0;
            box-shadow: 0 4px 30px rgba(0,0,0,0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .top-navbar .brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            letter-spacing: -1px;
        }
        .top-navbar .brand i {
            color: #ffd700;
        }
        .top-navbar .brand span {
            color: #ffd700;
        }
        .top-navbar .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .top-navbar .nav-link:hover {
            color: #fff !important;
            background: rgba(255,255,255,0.1);
        }
        .top-navbar .nav-link i {
            margin-right: 6px;
        }
        .top-navbar .user-dropdown .dropdown-toggle {
            color: #fff !important;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .top-navbar .user-dropdown .dropdown-toggle:hover {
            background: rgba(255,255,255,0.1);
        }
        .top-navbar .btn-logout {
            background: rgba(255,71,87,0.2);
            color: #ff4757 !important;
            border: 1px solid rgba(255,71,87,0.3);
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .top-navbar .btn-logout:hover {
            background: #ff4757;
            color: #fff !important;
            border-color: #ff4757;
        }
        .top-navbar .btn-view-site {
            background: rgba(255,215,0,0.15);
            color: #ffd700 !important;
            border: 1px solid rgba(255,215,0,0.3);
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .top-navbar .btn-view-site:hover {
            background: #ffd700;
            color: #1a1a2e !important;
        }

        /* ============ DASHBOARD HEADER ============ */
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 0 50px;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
            border-radius: 0 0 30px 30px;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        }
        .dashboard-header .welcome-text h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .dashboard-header .welcome-text p {
            opacity: 0.9;
            font-size: 1.05rem;
        }
        .dashboard-header .welcome-text .user-badge {
            background: rgba(255,255,255,0.2);
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-block;
        }
        .dashboard-header .header-actions .btn {
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .dashboard-header .header-actions .btn-submit {
            background: #ffd700;
            color: #1a1a2e;
            border: none;
        }
        .dashboard-header .header-actions .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
        }
        .dashboard-header .header-actions .btn-view {
            background: rgba(255,255,255,0.15);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .dashboard-header .header-actions .btn-view:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-3px);
        }
        
        /* ============ STATISTICS ============ */
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            border: none;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        .stat-card .stat-icon {
            width: 55px;
            height: 55px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin: 5px 0;
        }
        .stat-card .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .stat-card .stat-trend {
            font-size: 0.75rem;
            padding: 2px 10px;
            border-radius: 20px;
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        /* ============ PROFILE CARD ============ */
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            border: none;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2.5rem;
            color: white;
            border: 4px solid #fff;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        /* ============ POST CARDS ============ */
        .post-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            border-left: 4px solid #007bff;
            transition: all 0.3s ease;
        }
        .post-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .post-card.pending { border-left-color: #ffc107; }
        .post-card.approved { border-left-color: #28a745; }
        
        /* ============ SUBMIT FORM ============ */
        .submit-form {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            border: none;
        }
        .submit-form .form-control,
        .submit-form .form-select {
            border-radius: 10px;
            border: 2px solid #e8e8e8;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        .submit-form .form-control:focus,
        .submit-form .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        
        /* ============ NEWS CARDS ============ */
        .news-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            height: 100%;
        }
        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        .news-card img {
            height: 180px;
            object-fit: cover;
            width: 100%;
        }
        
        /* ============ BUTTONS ============ */
        .btn-primary-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .btn-success-gradient {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-success-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(17, 153, 142, 0.4);
            color: white;
        }
        
        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .dashboard-header .welcome-text h1 {
                font-size: 1.8rem;
            }
            .dashboard-header {
                padding: 25px 0 35px;
            }
            .top-navbar .brand {
                font-size: 1.3rem;
            }
            .stat-card .stat-number {
                font-size: 1.5rem;
            }
            .dashboard-header .header-actions .btn {
                padding: 8px 16px;
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 576px) {
            .top-navbar .nav-link {
                padding: 6px 12px;
                font-size: 0.85rem;
            }
            .top-navbar .btn-logout,
            .top-navbar .btn-view-site {
                padding: 6px 14px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>

<!-- ============ TOP NAVBAR - PREMIUM ============ -->
<nav class="top-navbar">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-3 col-md-4 col-6">
                <a href="index.php" class="brand">
                    <i class="fas fa-newspaper"></i> News<span>Verse</span>
                </a>
            </div>
            <div class="col-lg-9 col-md-8 col-6">
                <div class="d-flex align-items-center justify-content-end gap-2 gap-md-3">
                    <a href="index.php" class="nav-link d-none d-md-inline">
                        <i class="fas fa-home"></i> Home
                    </a>
                    
                    <!-- Categories Dropdown -->
                    <div class="dropdown d-none d-md-inline">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-folder"></i> Categories
                        </a>
                        <ul class="dropdown-menu">
                            <?php 
                            $cats = $pdo->query("SELECT * FROM categories WHERE is_active = 1 LIMIT 10");
                            while($cat = $cats->fetch()): 
                            ?>
                            <li><a href="category.php?slug=<?= $cat['slug'] ?>" class="dropdown-item">
                                <i class="<?= $cat['icon_class'] ?>"></i> <?= $cat['name'] ?>
                            </a></li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                    
                    <!-- User Dropdown -->
                    <div class="dropdown user-dropdown">
                        <a class="dropdown-toggle text-decoration-none" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-lg"></i>
                            <span class="d-none d-sm-inline"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#profile"><i class="fas fa-user"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#submit"><i class="fas fa-plus-circle"></i> Submit News</a></li>
                            <li><a class="dropdown-item" href="#my-posts"><i class="fas fa-list"></i> My Posts</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                    
                    <a href="logout.php" class="btn-logout text-decoration-none d-none d-sm-inline-block">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                    
                    <a href="index.php" class="btn-view-site text-decoration-none d-none d-lg-inline-block">
                        <i class="fas fa-eye"></i> View Site
                    </a>
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="navbar-toggler d-md-none border-0 p-0" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">
                        <i class="fas fa-bars text-white fs-4"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Nav -->
        <div class="collapse" id="mobileNav">
            <div class="mt-3 pt-3 border-top border-light border-opacity-10">
                <a href="index.php" class="nav-link d-block"><i class="fas fa-home"></i> Home</a>
                <a href="#profile" class="nav-link d-block"><i class="fas fa-user"></i> Profile</a>
                <a href="#submit" class="nav-link d-block"><i class="fas fa-plus-circle"></i> Submit News</a>
                <a href="#my-posts" class="nav-link d-block"><i class="fas fa-list"></i> My Posts</a>
                <a href="logout.php" class="nav-link d-block text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</nav>

<!-- ============ DASHBOARD HEADER - PREMIUM ============ -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 col-md-8">
                <div class="welcome-text">
                    <div class="user-badge mb-2">
                        <i class="fas fa-check-circle"></i> <?= $user['approved'] ? 'Verified Member' : 'Pending Approval' ?>
                    </div>
                    <h1 class="fw-bold">
                        Welcome Back, <span style="color: #ffd700;"><?= htmlspecialchars($_SESSION['full_name']) ?></span>!
                    </h1>
                    <p class="mb-0">
                        <i class="fas fa-user me-1"></i> @<?= htmlspecialchars($user['username']) ?> | 
                        <i class="fas fa-calendar-alt me-1"></i> <?= date('l, d F Y') ?>
                    </p>
                    <div class="mt-2">
                        <span class="badge bg-light text-dark me-2">
                            <i class="fas fa-file-alt"></i> <?= count($user_posts) ?> Posts
                        </span>
                        <span class="badge bg-success me-2">
                            <i class="fas fa-check-circle"></i> <?= $approved_count ?> Approved
                        </span>
                        <?php if($pending_count > 0): ?>
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-clock"></i> <?= $pending_count ?> Pending
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-4 text-md-end mt-3 mt-md-0">
                <div class="header-actions">
                    <a href="#submit" class="btn btn-submit">
                        <i class="fas fa-plus-circle"></i> Submit News
                    </a>
                    <a href="index.php" class="btn btn-view ms-2">
                        <i class="fas fa-eye"></i> View Site
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============ MAIN CONTENT ============ -->
<div class="container">

    <!-- Alert Messages -->
    <?php if($message): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3">
            <i class="fas fa-check-circle me-2"></i> <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3">
            <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ============ STATISTICS CARDS ============ -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-2">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-number"><?= count($user_posts) ?></div>
                        <div class="stat-label">Total Posts</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i> +<?= count($user_posts) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success mb-2">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?= $approved_count ?></div>
                        <div class="stat-label">Approved</div>
                    </div>
                    <div class="stat-trend" style="background: #e8f5e9; color: #2e7d32;">
                        <i class="fas fa-check"></i> Active
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning mb-2">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?= $pending_count ?></div>
                        <div class="stat-label">Pending Review</div>
                    </div>
                    <div class="stat-trend" style="background: #fff3e0; color: #e65100;">
                        <i class="fas fa-hourglass-half"></i> Waiting
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info mb-2">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-number" style="font-size: 1.5rem;"><?= date('d M Y') ?></div>
                        <div class="stat-label">Today's Date</div>
                    </div>
                    <div class="stat-trend" style="background: #e3f2fd; color: #0d47a1;">
                        <i class="fas fa-calendar"></i> <?= date('l') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- ============ LEFT COLUMN ============ -->
        <div class="col-lg-4">
            
            <!-- Profile Card -->
            <div class="profile-card mb-4" id="profile">
                <div class="text-center">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h5 class="fw-bold mb-0"><?= htmlspecialchars($user['full_name']) ?></h5>
                    <p class="text-muted small">@<?= htmlspecialchars($user['username']) ?></p>
                    <div class="mb-3">
                        <span class="badge bg-<?= $user['approved'] ? 'success' : 'warning' ?>">
                            <?= $user['approved'] ? '✅ Verified Member' : '⏳ Pending Approval' ?>
                        </span>
                    </div>
                </div>
                <hr>
                <div class="row g-2">
                    <div class="col-12">
                        <small class="text-muted d-block"><i class="fas fa-envelope me-1"></i> Email</small>
                        <span class="fw-medium"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block"><i class="fas fa-calendar-alt me-1"></i> Member Since</small>
                        <span class="fw-medium"><?= date('d F Y', strtotime($user['created_at'])) ?></span>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block"><i class="fas fa-circle me-1"></i> Status</small>
                        <span class="badge bg-<?= $user['status'] == 'active' ? 'success' : 'warning' ?>">
                            <?= ucfirst($user['status']) ?>
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Total Posts</small>
                        <strong><?= count($user_posts) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Approved</small>
                        <strong class="text-success"><?= $approved_count ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Pending</small>
                        <strong class="text-warning"><?= $pending_count ?></strong>
                    </div>
                </div>
            </div>
            
            <!-- Submit Post Form -->
            <div class="submit-form mb-4" id="submit">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-plus-circle text-success"></i> Submit News
                </h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Category <span class="text-danger">*</span></label>
                       <select name="category" class="form-select" required>
                        <option value="">— Select Category —</option>
                        <?php 
                        $cat_list = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
                        while($cat = $cat_list->fetch()): 
                        ?>
                            <option value="<?= htmlspecialchars($cat['name']) ?>" 
                                <?= isset($_POST['category']) && $_POST['category'] == $cat['name'] ? 'selected' : '' ?>>
                                <i class="<?= $cat['icon_class'] ?>"></i> <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" 
                               value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" 
                               placeholder="Enter news title..." required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Author Name <span class="text-danger">*</span></label>
                        <input type="text" name="author_name" class="form-control" 
                               value="<?= isset($_POST['author_name']) ? htmlspecialchars($_POST['author_name']) : htmlspecialchars($user['full_name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Content <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" rows="5" 
                                  placeholder="Write your news content here..." required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Main Image <span class="text-danger">*</span></label>
                        <input type="file" name="main_image" class="form-control" accept="image/*" required>
                        <small class="text-muted">Upload a featured image for your news</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Author Image <small class="text-muted">(Optional)</small></label>
                        <input type="file" name="author_image" class="form-control" accept="image/*">
                        <small class="text-muted">Upload a profile image for the author</small>
                    </div>
                    
                    <button type="submit" name="submit_post" class="btn btn-success-gradient">
                        <i class="fas fa-paper-plane"></i> Submit for Approval
                    </button>
                    <p class="text-muted small mt-2 mb-0 text-center">
                        <i class="fas fa-info-circle"></i> Your post will be reviewed by admin before publishing
                    </p>
                </form>
            </div>
            
            <!-- Categories Widget -->
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h5 class="fw-bold border-bottom pb-2">
                    <i class="fas fa-folder-open text-primary"></i> Browse Categories
                </h5>
                <?php while($cat = $categories->fetch()): ?>
                    <a href="category.php?slug=<?= $cat['slug'] ?>" 
                       class="d-flex justify-content-between align-items-center text-decoration-none py-2 border-bottom">
                        <span>
                            <i class="<?= $cat['icon_class'] ?>" style="color: <?= $cat['color'] ?>"></i>
                            <?= htmlspecialchars($cat['name']) ?>
                        </span>
                        <span class="badge bg-secondary">
                            <?php 
                            $count = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE category = ? AND approved = 1 AND status = 'published'");
                            $count->execute([$cat['name']]);
                            echo $count->fetchColumn();
                            ?>
                        </span>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- ============ RIGHT COLUMN ============ -->
        <div class="col-lg-8">
            
            <!-- My Posts Tabs -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" id="my-posts">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <ul class="nav nav-tabs border-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#all-posts">
                                <i class="fas fa-list"></i> All Posts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#pending-posts">
                                <i class="fas fa-clock text-warning"></i> Pending
                                <?php if($pending_count > 0): ?>
                                    <span class="badge bg-warning text-dark ms-1"><?= $pending_count ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#approved-posts">
                                <i class="fas fa-check-circle text-success"></i> Approved
                                <?php if($approved_count > 0): ?>
                                    <span class="badge bg-success ms-1"><?= $approved_count ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content">
                        <!-- All Posts -->
                        <div class="tab-pane fade show active" id="all-posts">
                            <?php if(count($user_posts) > 0): ?>
                                <?php foreach($user_posts as $post): ?>
                                    <div class="post-card <?= $post['approved'] ? 'approved' : 'pending' ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="post-title"><?= htmlspecialchars($post['title']) ?></div>
                                                <div class="post-meta">
                                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($post['category']) ?> | 
                                                    <i class="far fa-calendar-alt"></i> <?= date('d M Y, h:i A', strtotime($post['submission_date'])) ?>
                                                    <?php if($post['view_count'] > 0): ?>
                                                        | <i class="fas fa-eye"></i> <?= $post['view_count'] ?>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="small text-muted mt-2 mb-0">
                                                    <?= htmlspecialchars(mb_strimwidth($post['content'], 0, 150, '...')) ?>
                                                </p>
                                            </div>
                                            <div class="ms-3 text-end">
                                                <span class="badge bg-<?= $post['approved'] ? 'success' : 'warning' ?>">
                                                    <?= $post['approved'] ? '✅ Approved' : '⏳ Pending' ?>
                                                </span>
                                                <?php if($post['approved']): ?>
                                                    <br>
                                                    <a href="view_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-file-alt fa-3x text-muted mb-3 d-block"></i>
                                    <h6>No posts submitted yet</h6>
                                    <p class="text-muted">Start by submitting your first news article.</p>
                                    <a href="#submit" class="btn btn-primary-gradient">
                                        <i class="fas fa-plus-circle"></i> Submit Your First Post
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Pending Posts -->
                        <div class="tab-pane fade" id="pending-posts">
                            <?php 
                            $pending_posts = array_filter($user_posts, function($p) { return $p['approved'] == 0; });
                            if(count($pending_posts) > 0): 
                            ?>
                                <?php foreach($pending_posts as $post): ?>
                                    <div class="post-card pending">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="post-title"><?= htmlspecialchars($post['title']) ?></div>
                                                <div class="post-meta">
                                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($post['category']) ?> | 
                                                    <i class="far fa-calendar-alt"></i> <?= date('d M Y, h:i A', strtotime($post['submission_date'])) ?>
                                                </div>
                                                <p class="small text-muted mt-2 mb-0">
                                                    <?= htmlspecialchars(mb_strimwidth($post['content'], 0, 120, '...')) ?>
                                                </p>
                                            </div>
                                            <div class="ms-3">
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3 d-block"></i>
                                    <p class="text-muted">No pending posts. All your posts are approved!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Approved Posts -->
                        <div class="tab-pane fade" id="approved-posts">
                            <?php 
                            $approved_posts = array_filter($user_posts, function($p) { return $p['approved'] == 1; });
                            if(count($approved_posts) > 0): 
                            ?>
                                <?php foreach($approved_posts as $post): ?>
                                    <div class="post-card approved">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="post-title"><?= htmlspecialchars($post['title']) ?></div>
                                                <div class="post-meta">
                                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($post['category']) ?> | 
                                                    <i class="far fa-calendar-alt"></i> <?= date('d M Y, h:i A', strtotime($post['submission_date'])) ?>
                                                    <?php if($post['view_count'] > 0): ?>
                                                        | <i class="fas fa-eye"></i> <?= $post['view_count'] ?>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="small text-muted mt-2 mb-0">
                                                    <?= htmlspecialchars(mb_strimwidth($post['content'], 0, 120, '...')) ?>
                                                </p>
                                            </div>
                                            <div class="ms-3 text-end">
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Approved
                                                </span>
                                                <br>
                                                <a href="view_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-hourglass-start fa-3x text-warning mb-3 d-block"></i>
                                    <p class="text-muted">No approved posts yet. Your posts are being reviewed.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Latest Approved News -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-newspaper text-success"></i> Latest Approved News
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php if(count($published_posts) > 0): ?>
                        <div class="row g-3">
                            <?php foreach(array_slice($published_posts, 0, 3) as $post): ?>
                                <div class="col-md-4">
                                    <div class="news-card">
                                        <img src="<?= htmlspecialchars($post['main_image']) ?>" alt="News">
                                        <div class="card-body">
                                            <h6 class="card-title text-truncate-2">
                                                <a href="view_post.php?id=<?= $post['id'] ?>" class="text-dark text-decoration-none">
                                                    <?= htmlspecialchars($post['title']) ?>
                                                </a>
                                            </h6>
                                            <p class="card-text small text-truncate-2 text-muted">
                                                <?= htmlspecialchars(mb_strimwidth($post['content'], 0, 80, '...')) ?>
                                            </p>
                                            <small class="text-muted">
                                                <i class="far fa-calendar-alt"></i> <?= date('d M Y', strtotime($post['created_at'])) ?>
                                            </small>
                                            <br>
                                            <a href="view_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                                                Read More <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted">No approved news posts available yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============ FOOTER ============ -->
<footer class="bg-dark text-white-50 mt-5 py-4">
    <div class="container text-center">
        <p class="mb-0 small">
            &copy; <?= date('Y') ?> <span class="text-warning">NewsVerse</span> — 
            <i class="fas fa-heart text-danger"></i> Made with passion
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>