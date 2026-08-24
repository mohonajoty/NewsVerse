<?php
session_start();
include 'db.php';

// Function to get post image with fallback
function getPostImage($post) {
    if(isset($post['main_image']) && !empty($post['main_image']) && file_exists($post['main_image'])) {
        return htmlspecialchars($post['main_image']);
    }
    return 'uploads/default.jpg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NewsVerse - Smart Digital Media Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8f9fa; }
        
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            padding: 12px 0;
        }
        .brand {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1a1a2e;
            text-decoration: none;
        }
        .brand i { color: #667eea; }
        .brand span { color: #764ba2; }
        .nav-link { 
            color: #555 !important;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            color: #667eea !important;
            background: rgba(102, 126, 234, 0.08);
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .btn-outline-custom {
            border: 2px solid #667eea;
            color: #667eea;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-outline-custom:hover {
            background: #667eea;
            color: white;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border-radius: 12px;
            padding: 8px;
        }
        .dropdown-item {
            border-radius: 8px;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }
        .dropdown-item:hover {
            background: rgba(102, 126, 234, 0.08);
            color: #667eea;
        }
        .dropdown-item i { width: 20px; }
        .search-box {
            border-radius: 50px;
            padding: 8px 20px;
            border: 2px solid #e8e8e8;
            background: white;
            transition: all 0.3s ease;
        }
        .search-box:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        
        .navbar-dashboard {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%) !important;
        }
        .navbar-dashboard .brand {
            color: white !important;
        }
        .navbar-dashboard .brand span {
            color: #ffd700 !important;
        }
        .navbar-dashboard .nav-link {
            color: rgba(255,255,255,0.8) !important;
        }
        .navbar-dashboard .nav-link:hover {
            color: #fff !important;
            background: rgba(255,255,255,0.1);
        }
        .navbar-dashboard .user-avatar {
            border: 2px solid #ffd700;
        }
        .navbar-dashboard .dropdown-menu {
            background: #1a1a2e;
        }
        .navbar-dashboard .dropdown-item {
            color: rgba(255,255,255,0.8);
        }
        .navbar-dashboard .dropdown-item:hover {
            background: rgba(255,215,0,0.15);
            color: #ffd700;
        }
        
        .footer {
            background: #1a1a2e;
            color: rgba(255,255,255,0.7);
            padding: 50px 0 20px;
            margin-top: 50px;
        }
        .footer a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .footer a:hover {
            color: #ffd700;
        }
        .footer .brand {
            color: white;
        }
        .footer .brand span { color: #ffd700; }
        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
        }
        .social-icon:hover {
            background: #ffd700;
            color: #1a1a2e;
            transform: translateY(-3px);
        }
    </style>
</head>
<body>

<?php 
$is_dashboard = strpos($_SERVER['PHP_SELF'], 'dashboard') !== false || 
                strpos($_SERVER['PHP_SELF'], 'admin_') !== false ||
                strpos($_SERVER['PHP_SELF'], 'manage_') !== false;
?>

<nav class="navbar navbar-expand-lg navbar-custom <?= $is_dashboard ? 'navbar-dashboard' : '' ?> sticky-top">
    <div class="container">
        <a href="index.php" class="brand">
            <i class="fas fa-newspaper"></i> News<span>Verse</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categories</a>
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
                </li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-fire"></i> Trending</a></li>
            </ul>
            
            <div class="d-flex align-items-center gap-2">
                <form class="d-none d-md-block">
                    <input type="text" class="search-box" placeholder="Search news...">
                </form>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <a href="#" class="text-decoration-none" data-bs-toggle="dropdown">
                            <div class="user-avatar">
                                <?= substr($_SESSION['full_name'], 0, 1) ?>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if($_SESSION['role'] == 'admin'): ?>
                                <li><a class="dropdown-item" href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Admin Panel</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="user_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-custom btn-sm">Login</a>
                    <a href="register.php" class="btn btn-primary-custom btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main>