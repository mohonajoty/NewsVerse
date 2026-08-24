<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Function to generate slug from title
function generateSlug($string) {
    // Convert to lowercase
    $string = strtolower($string);
    
    // Remove special characters
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    
    // Replace spaces with hyphens
    $string = preg_replace('/[\s]+/', '-', $string);
    
    // Remove multiple hyphens
    $string = preg_replace('/-+/', '-', $string);
    
    // Trim hyphens from ends
    $string = trim($string, '-');
    
    return $string;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $category = trim($_POST['category'] ?? '');
    $is_latest = isset($_POST['is_latest']) ? 1 : 0;
    $title = trim($_POST['title'] ?? '');
    $author_name = trim($_POST['author_name'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    // Validation
    if (empty($category) || empty($title) || empty($author_name) || empty($content)) {
        $error = "All fields are required!";
    } elseif (!isset($_FILES['main_image']) || $_FILES['main_image']['error'] == 4) {
        $error = "Main image is required!";
    } else {
        // Generate slug from title
        $slug = generateSlug($title);
        
        // Check if slug already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE slug = ?");
        $stmt->execute([$slug]);
        $count = $stmt->fetchColumn();
        
        // If slug exists, append a number
        if ($count > 0) {
            $slug = $slug . '-' . time();
        }
        
        // Image Upload
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Upload Main Image
        $main_image_name = basename($_FILES["main_image"]["name"]);
        $main_image_ext = strtolower(pathinfo($main_image_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($main_image_ext, $allowed_exts)) {
            $error = "Only JPG, JPEG, PNG, GIF, and WEBP files are allowed!";
        } else {
            $main_image = $target_dir . time() . "_main_" . $main_image_name;
            if (move_uploaded_file($_FILES["main_image"]["tmp_name"], $main_image)) {
                
                // Upload Author Image (Optional)
                $author_image = "";
                if (!empty($_FILES["author_image"]["name"]) && $_FILES["author_image"]["error"] == 0) {
                    $auth_img_name = basename($_FILES["author_image"]["name"]);
                    $auth_ext = strtolower(pathinfo($auth_img_name, PATHINFO_EXTENSION));
                    if (in_array($auth_ext, $allowed_exts)) {
                        $author_image = $target_dir . time() . "_auth_" . $auth_img_name;
                        move_uploaded_file($_FILES["author_image"]["tmp_name"], $author_image);
                    }
                }
                
                // Insert into database with slug
                try {
                    $stmt = $pdo->prepare("INSERT INTO posts (category, is_latest, title, slug, author_name, author_image, content, main_image, created_by, status, approved) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', 1)");
                    $stmt->execute([$category, $is_latest, $title, $slug, $author_name, $author_image, $content, $main_image, $_SESSION['user_id']]);
                    
                    $success = "Post created successfully!";
                    
                    // Clear form data
                    $_POST = array();
                    
                } catch(PDOException $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            } else {
                $error = "Failed to upload main image!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Create Post - NewsVerse Admin</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        .btn-submit {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(17, 153, 142, 0.4);
            color: white;
        }
        .top-actions .btn {
            border-radius: 10px;
            padding: 8px 18px;
            font-weight: 500;
        }
        .required-star {
            color: #dc3545;
        }
        .slug-preview {
            background: #f8f9fa;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .slug-preview strong {
            color: #333;
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
                <i class="fas fa-plus-circle me-2"></i> Create New Post
            </h4>
            <p class="mb-0 opacity-75 small">Fill in the details below to publish a new article</p>
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
            
            <form method="POST" enctype="multipart/form-data" id="postForm">
                
                <!-- Category -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-tags text-primary"></i> Category <span class="required-star">*</span>
                    </label>
                    <select name="category" class="form-select" required>
                        <option value="">— Select a Category —</option>
                        <option value="Technology" <?= isset($_POST['category']) && $_POST['category'] == 'Technology' ? 'selected' : '' ?>>Technology</option>
                        <option value="Business" <?= isset($_POST['category']) && $_POST['category'] == 'Business' ? 'selected' : '' ?>>Business</option>
                        <option value="Health" <?= isset($_POST['category']) && $_POST['category'] == 'Health' ? 'selected' : '' ?>>Health</option>
                        <option value="Science" <?= isset($_POST['category']) && $_POST['category'] == 'Science' ? 'selected' : '' ?>>Science</option>
                        <option value="Sports" <?= isset($_POST['category']) && $_POST['category'] == 'Sports' ? 'selected' : '' ?>>Sports</option>
                        <option value="Entertainment" <?= isset($_POST['category']) && $_POST['category'] == 'Entertainment' ? 'selected' : '' ?>>Entertainment</option>
                        <option value="World" <?= isset($_POST['category']) && $_POST['category'] == 'World' ? 'selected' : '' ?>>World</option>
                        <option value="Politics" <?= isset($_POST['category']) && $_POST['category'] == 'Politics' ? 'selected' : '' ?>>Politics</option>
                        <option value="Education" <?= isset($_POST['category']) && $_POST['category'] == 'Education' ? 'selected' : '' ?>>Education</option>
                        <option value="Lifestyle" <?= isset($_POST['category']) && $_POST['category'] == 'Lifestyle' ? 'selected' : '' ?>>Lifestyle</option>
                    </select>
                </div>
                
                <!-- Title -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-heading text-primary"></i> Title <span class="required-star">*</span>
                    </label>
                    <input type="text" name="title" class="form-control" 
                           placeholder="Enter article title..." 
                           value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" 
                           id="titleInput" required>
                    <div class="slug-preview">
                        <i class="fas fa-link"></i> Slug: <strong id="slugPreview"><?= isset($_POST['title']) ? generateSlug($_POST['title']) : 'your-article-slug' ?></strong>
                    </div>
                </div>
                
                <!-- Author Name -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-user-edit text-primary"></i> Author Name <span class="required-star">*</span>
                    </label>
                    <input type="text" name="author_name" class="form-control" 
                           placeholder="Enter author name..." 
                           value="<?= isset($_POST['author_name']) ? htmlspecialchars($_POST['author_name']) : '' ?>" required>
                </div>
                
                <!-- Author Image -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-user-circle text-primary"></i> Author Image <small class="text-muted">(Optional)</small>
                    </label>
                    <input type="file" name="author_image" class="form-control" accept="image/*">
                    <small class="text-muted">Recommended: Square image, max 2MB</small>
                </div>
                
                <!-- Main Image -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-image text-primary"></i> Main Image <span class="required-star">*</span>
                    </label>
                    <input type="file" name="main_image" class="form-control" accept="image/*" required>
                    <small class="text-muted">Recommended: 1200x800 pixels, max 5MB (JPG, PNG, GIF, WEBP)</small>
                </div>
                
                <!-- Content -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-align-left text-primary"></i> Content <span class="required-star">*</span>
                    </label>
                    <textarea name="content" class="form-control" rows="8" 
                              placeholder="Write your article content here..." required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
                </div>
                
                <!-- Latest News Checkbox -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="is_latest" id="latest" 
                           <?= isset($_POST['is_latest']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="latest">
                        <i class="fas fa-star text-warning"></i> Mark as Latest News (Featured)
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane me-2"></i> Publish News
                </button>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Your post will be published immediately
                    </small>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live slug preview
document.getElementById('titleInput').addEventListener('input', function() {
    const title = this.value;
    const slug = title.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s]+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
    document.getElementById('slugPreview').textContent = slug || 'your-article-slug';
});
</script>

</body>
</html>