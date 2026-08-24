<?php
session_start();
include 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle bulk actions
$bulk_message = '';
if(isset($_POST['bulk_action']) && isset($_POST['post_ids'])) {
    $action = $_POST['bulk_action'];
    $post_ids = $_POST['post_ids'];
    $ids_string = implode(',', array_map('intval', $post_ids));
    
    try {
        if($action == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id IN ($ids_string)");
            $stmt->execute();
            $bulk_message = count($post_ids) . " posts deleted successfully!";
        } elseif($action == 'approve') {
            $stmt = $pdo->prepare("UPDATE posts SET approved = 1, status = 'published', approved_by = ?, approved_at = NOW() WHERE id IN ($ids_string)");
            $stmt->execute([$_SESSION['user_id']]);
            $bulk_message = count($post_ids) . " posts approved successfully!";
        } elseif($action == 'pending') {
            $stmt = $pdo->prepare("UPDATE posts SET approved = 0, status = 'pending' WHERE id IN ($ids_string)");
            $stmt->execute();
            $bulk_message = count($post_ids) . " posts moved to pending!";
        }
    } catch(PDOException $e) {
        $bulk_message = "Error: " . $e->getMessage();
    }
}

// Handle individual post actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $post_id = (int)$_GET['id'];
    
    try {
        if ($action == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->execute([$post_id]);
            $bulk_message = "Post deleted successfully!";
        } elseif ($action == 'approve') {
            $stmt = $pdo->prepare("UPDATE posts SET approved = 1, status = 'published', approved_by = ?, approved_at = NOW() WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $post_id]);
            $bulk_message = "Post approved successfully!";
        } elseif ($action == 'pending') {
            $stmt = $pdo->prepare("UPDATE posts SET approved = 0, status = 'pending' WHERE id = ?");
            $stmt->execute([$post_id]);
            $bulk_message = "Post moved to pending!";
        } elseif ($action == 'feature') {
            $stmt = $pdo->prepare("UPDATE posts SET is_latest = 1 WHERE id = ?");
            $stmt->execute([$post_id]);
            $bulk_message = "Post featured as latest!";
        } elseif ($action == 'unfeature') {
            $stmt = $pdo->prepare("UPDATE posts SET is_latest = 0 WHERE id = ?");
            $stmt->execute([$post_id]);
            $bulk_message = "Post unfeatured!";
        }
    } catch(PDOException $e) {
        $bulk_message = "Error: " . $e->getMessage();
    }
    
    header("Location: manage_posts.php?msg=" . urlencode($bulk_message));
    exit();
}

// Get filter parameters
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_filter = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT p.*, u.username as submitted_by_username 
          FROM posts p 
          LEFT JOIN users u ON p.submitted_by = u.id 
          WHERE 1=1";

$params = [];

if(!empty($category_filter)) {
    $query .= " AND p.category = ?";
    $params[] = $category_filter;
}

if(!empty($status_filter)) {
    if($status_filter == 'approved') {
        $query .= " AND p.approved = 1";
    } elseif($status_filter == 'pending') {
        $query .= " AND p.approved = 0";
    } elseif($status_filter == 'latest') {
        $query .= " AND p.is_latest = 1";
    }
}

if(!empty($search_filter)) {
    $query .= " AND (p.title LIKE ? OR p.content LIKE ? OR p.author_name LIKE ?)";
    $search = "%$search_filter%";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT DISTINCT category FROM posts ORDER BY category")->fetchAll();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Posts - NewsVerse Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; }
        .post-row:hover { background: #f8f9fa; }
        .post-row.pending { border-left: 4px solid #ffc107; }
        .post-row.approved { border-left: 4px solid #28a745; }
        .post-row.featured { border-left: 4px solid #dc3545; }
        .table th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .filter-section { background: white; padding: 15px; border-radius: 8px; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; }
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
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <a href="admin_create.php" class="btn btn-success btn-sm me-2">
                <i class="fas fa-plus"></i> New Post
            </a>
            <a href="logout.php" class="btn btn-danger btn-sm">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="fw-bold mb-0"><i class="fas fa-list text-primary"></i> Manage Posts</h5>
            <span class="badge bg-secondary"><?= count($posts) ?> posts</span>
        </div>
        <div class="card-body">
            <?php if($msg): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if($bulk_message): ?>
                <div class="alert alert-info alert-dismissible fade show">
                    <i class="fas fa-info-circle"></i> <?= htmlspecialchars($bulk_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Filter Section -->
            <div class="filter-section mb-4">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['category']) ?>" 
                                    <?= $category_filter == $cat['category'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="approved" <?= $status_filter == 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="latest" <?= $status_filter == 'latest' ? 'selected' : '' ?>>Featured</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search posts..." 
                               value="<?= htmlspecialchars($search_filter) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Bulk Actions -->
            <form method="POST" action="" id="bulkForm">
                <div class="d-flex gap-2 mb-3">
                    <select name="bulk_action" class="form-select" style="width: 200px;">
                        <option value="">Bulk Actions</option>
                        <option value="approve">Approve Selected</option>
                        <option value="pending">Move to Pending</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Apply bulk action?')">
                        <i class="fas fa-check-double"></i> Apply
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="selectAll()">
                        <i class="fas fa-check-square"></i> Select All
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="40"><input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()"></th>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Author</th>
                                <th>Submitted By</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Date</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($posts) > 0): ?>
                                <?php foreach($posts as $post): ?>
                                <tr class="post-row <?= $post['approved'] ? 'approved' : 'pending' ?> <?= $post['is_latest'] ? 'featured' : '' ?>">
                                    <td><input type="checkbox" name="post_ids[]" value="<?= $post['id'] ?>" class="post-checkbox"></td>
                                    <td><?= $post['id'] ?></td>
                                    <td><?= htmlspecialchars(mb_strimwidth($post['title'], 0, 30, '...')) ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($post['category']) ?></span></td>
                                    <td><?= htmlspecialchars($post['author_name']) ?></td>
                                    <td><?= htmlspecialchars($post['submitted_by_username'] ?? 'Admin') ?></td>
                                    <td>
                                        <?php if($post['approved']): ?>
                                            <span class="badge bg-success">Published</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                        <?php if($post['is_latest']): ?>
                                            <span class="badge bg-danger">Featured</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($post['view_count'] ?? 0) ?></td>
                                    <td><?= date('M d, Y', strtotime($post['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view_post.php?id=<?= $post['id'] ?>" class="btn btn-info" target="_blank" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="admin_edit.php?id=<?= $post['id'] ?>" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if(!$post['approved']): ?>
                                                <a href="?action=approve&id=<?= $post['id'] ?>" class="btn btn-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if($post['is_latest']): ?>
                                                <a href="?action=unfeature&id=<?= $post['id'] ?>" class="btn btn-secondary" title="Unfeature">
                                                    <i class="fas fa-star"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?action=feature&id=<?= $post['id'] ?>" class="btn btn-danger" title="Feature as Latest">
                                                    <i class="fas fa-star"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?action=delete&id=<?= $post['id'] ?>" class="btn btn-danger" 
                                               onclick="return confirm('Delete this post?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fas fa-newspaper fa-2x d-block mb-2"></i>
                                        No posts found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
            
            <!-- Quick Stats -->
            <div class="row mt-3 g-2">
                <div class="col-md-3">
                    <div class="bg-light p-2 rounded text-center">
                        <small class="text-muted">Total Posts</small>
                        <h6 class="mb-0"><?= count($posts) ?></h6>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="bg-light p-2 rounded text-center">
                        <small class="text-muted">Approved</small>
                        <h6 class="mb-0 text-success">
                            <?= count(array_filter($posts, function($p) { return $p['approved'] == 1; })) ?>
                        </h6>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="bg-light p-2 rounded text-center">
                        <small class="text-muted">Pending</small>
                        <h6 class="mb-0 text-warning">
                            <?= count(array_filter($posts, function($p) { return $p['approved'] == 0; })) ?>
                        </h6>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="bg-light p-2 rounded text-center">
                        <small class="text-muted">Featured</small>
                        <h6 class="mb-0 text-danger">
                            <?= count(array_filter($posts, function($p) { return $p['is_latest'] == 1; })) ?>
                        </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAllCheckboxes() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.post-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

function selectAll() {
    document.getElementById('selectAll').checked = true;
    toggleAllCheckboxes();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>