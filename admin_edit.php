<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// 1. Get the post ID from the URL
if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}
$id = (int)$_GET['id'];

// 2. Fetch the current data for this post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';
$success = '';

// 3. Handle the Update Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category = trim($_POST['category'] ?? '');
    $is_latest = isset($_POST['is_latest']) ? 1 : 0;
    $title = trim($_POST['title'] ?? '');
    $author_name = trim($_POST['author_name'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    // Validation
    if (empty($category) || empty($title) || empty($author_name) || empty($content)) {
        $error = "All fields are required!";
    } else {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Start building the update query
        $sql = "UPDATE posts SET category = ?, is_latest = ?, title = ?, author_name = ?, content = ?";
        $params = [$category, $is_latest, $title, $author_name, $content];
        
        // Check if a new Main Image was uploaded
        if (!empty($_FILES["main_image"]["name"]) && $_FILES["main_image"]["error"] == 0) {
            $main_image_name = basename($_FILES["main_image"]["name"]);
            $main_image_ext = strtolower(pathinfo($main_image_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($main_image_ext, $allowed_exts)) {
                $main_image = $target_dir . time() . "_main_" . $main_image_name;
                if (move_uploaded_file($_FILES["main_image"]["tmp_name"], $main_image)) {
                    $sql .= ", main_image = ?";
                    $params[] = $main_image;
                    
                    // Delete old main image
                    if (!empty($post['main_image']) && file_exists($post['main_image'])) {
                        unlink($post['main_image']);
                    }
                }
            }
        }
        
        // Check if a new Author Image was uploaded
        if (!empty($_FILES["author_image"]["name"]) && $_FILES["author_image"]["error"] == 0) {
            $auth_img_name = basename($_FILES["author_image"]["name"]);
            $auth_ext = strtolower(pathinfo($auth_img_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($auth_ext, $allowed_exts)) {
                $author_image = $target_dir . time() . "_auth_" . $auth_img_name;
                if (move_uploaded_file($_FILES["author_image"]["tmp_name"], $author_image)) {
                    $sql .= ", author_image = ?";
                    $params[] = $author_image;
                    
                    // Delete old author image
                    if (!empty($post['author_image']) && file_exists($post['author_image'])) {
                        unlink($post['author_image']);
                    }
                }
            }
        }
        
        // Add WHERE clause
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        // Execute the update
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $success = "Post updated successfully!";
            
            // Refresh post data
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch();
            
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Post - NewsVerse Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f0f2f5; }
        
        .form-container {
            max-width: 800px;
            margin: 40px auto;
        }
        .form-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
        }
        .form-card .card-header {
            background: linear-gradient(135deg, #f2994a 0%, #f2c94a 100%);
            color: #333;
            padding: 20px 30px;
            border: none;
        }
        .form-card .card-body {
            padding: 30px;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #333;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 10px 15px;
            border: 2px solid #e8e8e8;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #f2994a;
            box-shadow: 0 0 0 0.2rem rgba(242, 153, 74, 0.15);
        }
        .btn-update {
            background: linear-gradient(135deg, #f2994a 0%, #f2c94a 100%);
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            color: #333;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(242, 153, 74, 0.4);
            color: #333;
        }
        .required-star {
            color: #dc3545;
        }
        .current-image {
            max-width: 100px;
            max-height: 100px;
            border-radius: 8px;
            margin-top: 5px;
        }
        .top-actions .btn {
            border-radius: 10px;
            padding: 8px 18px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<!-- ============ TOP NAVBAR ============ -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="admin_dashboard.php">
            <i class="fas fa-newspaper me-2"></i> NewsVerse Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm me-2">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" target="_blank" class="btn btn-outline-light btn-sm me-2">
                        <i class="fas fa-eye"></i> View Site
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ============ MAIN CONTENT ============ -->
<div class="container form-container">
    
    <div class="form-card">
        <div class="card-header">
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-edit me-2"></i> Edit Post
            </h4>
            <p class="mb-0 opacity-75 small">Update the details of your article</p>
        </div>
        <div class="card-body">
            
            <!-- Error Alert -->
            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Success Alert -->
            <?php if($success): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 rounded-3">
                    <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                
                <!-- Category -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-tags text-primary"></i> Category <span class="required-star">*</span>
                    </label>
                    <select name="category" class="form-select" required>
                        <option value="">— Select a Category —</option>
                        <option value="Technology" <?= $post['category'] == 'Technology' ? 'selected' : '' ?>>Technology</option>
                        <option value="Business" <?= $post['category'] == 'Business' ? 'selected' : '' ?>>Business</option>
                        <option value="Health" <?= $post['category'] == 'Health' ? 'selected' : '' ?>>Health</option>
                        <option value="Science" <?= $post['category'] == 'Science' ? 'selected' : '' ?>>Science</option>
                        <option value="Sports" <?= $post['category'] == 'Sports' ? 'selected' : '' ?>>Sports</option>
                        <option value="Entertainment" <?= $post['category'] == 'Entertainment' ? 'selected' : '' ?>>Entertainment</option>
                        <option value="World" <?= $post['category'] == 'World' ? 'selected' : '' ?>>World</option>
                        <option value="Politics" <?= $post['category'] == 'Politics' ? 'selected' : '' ?>>Politics</option>
                        <option value="Education" <?= $post['category'] == 'Education' ? 'selected' : '' ?>>Education</option>
                        <option value="Lifestyle" <?= $post['category'] == 'Lifestyle' ? 'selected' : '' ?>>Lifestyle</option>
                    </select>
                </div>
                
                <!-- Title -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-heading text-primary"></i> Title <span class="required-star">*</span>
                    </label>
                    <input type="text" name="title" class="form-control" 
                           value="<?= htmlspecialchars($post['title']) ?>" required>
                </div>
                
                <!-- Author Name -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-user-edit text-primary"></i> Author Name <span class="required-star">*</span>
                    </label>
                    <input type="text" name="author_name" class="form-control" 
                           value="<?= htmlspecialchars($post['author_name']) ?>" required>
                </div>
                
                <!-- Author Image -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-user-circle text-primary"></i> Author Image <small class="text-muted">(Leave empty to keep current)</small>
                    </label>
                    <input type="file" name="author_image" class="form-control" accept="image/*">
                    <?php if($post['author_image']): ?>
                        <div class="mt-2">
                            <small class="text-muted">Current: </small>
                            <a href="<?= htmlspecialchars($post['author_image']) ?>" target="_blank">
                                <img src="<?= htmlspecialchars($post['author_image']) ?>" class="current-image" alt="Author Image">
                            </a>
                        </div>
                    <?php endif; ?>
                    <small class="text-muted">Recommended: Square image, max 2MB</small>
                </div>
                
                <!-- Main Image -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-image text-primary"></i> Main Image <small class="text-muted">(Leave empty to keep current)</small>
                    </label>
                    <input type="file" name="main_image" class="form-control" accept="image/*">
                    <?php if($post['main_image']): ?>
                        <div class="mt-2">
                            <small class="text-muted">Current: </small>
                            <a href="<?= htmlspecialchars($post['main_image']) ?>" target="_blank">
                                <img src="<?= htmlspecialchars($post['main_image']) ?>" class="current-image" alt="Main Image">
                            </a>
                        </div>
                    <?php endif; ?>
                    <small class="text-muted">Recommended: 1200x800 pixels, max 5MB (JPG, PNG, GIF, WEBP)</small>
                </div>
                
                <!-- Content -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-align-left text-primary"></i> Content <span class="required-star">*</span>
                    </label>
                    <textarea name="content" class="form-control" rows="8" required><?= htmlspecialchars($post['content']) ?></textarea>
                </div>
                
                <!-- Latest News Checkbox -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="is_latest" id="latest" <?= $post['is_latest'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="latest">
                        <i class="fas fa-star text-warning"></i> Mark as Latest News (Featured)
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-update">
                    <i class="fas fa-save me-2"></i> Update News
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>