<?php 
include 'header.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$current_user_id = $is_logged_in ? $_SESSION['user_id'] : 0;
$is_admin = $is_logged_in && $_SESSION['role'] == 'admin';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Redirect if no ID
if($id == 0) {
    header("Location: index.php");
    exit();
}

// Get post with proper permissions
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

// Check if post exists
if(!$post) {
    ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
                        <h4>Post Not Found</h4>
                        <p class="text-muted">The post you're looking for doesn't exist or has been removed.</p>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    include 'footer.php';
    exit();
}

// Check permission to view post
$can_view = false;
$pending_message = '';

if($post['approved'] == 1 && $post['status'] == 'published') {
    $can_view = true;
} elseif($is_admin) {
    $can_view = true;
    if($post['approved'] == 0) {
        $pending_message = '<div class="alert alert-warning"><i class="fas fa-clock"></i> This post is pending approval. Only admins can see it.</div>';
    }
} elseif($is_logged_in && $post['submitted_by'] == $current_user_id) {
    $can_view = true;
    if($post['approved'] == 0) {
        $pending_message = '<div class="alert alert-info"><i class="fas fa-clock"></i> Your post is pending admin approval.</div>';
    }
}

if(!$can_view) {
    ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-lock fa-4x text-warning mb-3"></i>
                        <h4>Access Denied</h4>
                        <p class="text-muted">This post is <?= $post['approved'] ? 'not published' : 'pending approval' ?>.</p>
                        <?php if(!$is_logged_in): ?>
                            <a href="login.php" class="btn btn-primary">Login to View</a>
                        <?php endif; ?>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    include 'footer.php';
    exit();
}

// ==================== LIKE SYSTEM (LOGIN REQUIRED) ====================
// Check if user already liked this post (only if logged in)
$user_liked = false;
if($is_logged_in) {
    // Check if likes table exists, use post_likes if not
    $table_name = 'likes';
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'post_likes'");
        if($stmt->rowCount() > 0) {
            $table_name = 'post_likes';
        }
    } catch(PDOException $e) {
        $table_name = 'likes';
    }
    
    $stmt = $pdo->prepare("SELECT * FROM $table_name WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$id, $current_user_id]);
    $user_liked = $stmt->fetch() ? true : false;
}

// Handle Like/Unlike (only for logged in users)
if($is_logged_in && isset($_GET['action']) && $_GET['action'] == 'like' && isset($_GET['post_id'])) {
    $post_id = (int)$_GET['post_id'];
    
    // Determine table name
    $table_name = 'likes';
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'post_likes'");
        if($stmt->rowCount() > 0) {
            $table_name = 'post_likes';
        }
    } catch(PDOException $e) {
        $table_name = 'likes';
    }
    
    if($post_id == $id) {
        if(!$user_liked) {
            // Add like
            $stmt = $pdo->prepare("INSERT INTO $table_name (post_id, user_id) VALUES (?, ?)");
            $stmt->execute([$post_id, $current_user_id]);
            
            // Update likes count
            $stmt = $pdo->prepare("UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?");
            $stmt->execute([$post_id]);
            
            $user_liked = true;
        } else {
            // Remove like (unlike)
            $stmt = $pdo->prepare("DELETE FROM $table_name WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$post_id, $current_user_id]);
            
            // Update likes count
            $stmt = $pdo->prepare("UPDATE posts SET likes_count = likes_count - 1 WHERE id = ?");
            $stmt->execute([$post_id]);
            
            $user_liked = false;
        }
        
        // Refresh post data
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        
        // Redirect to remove action from URL
        header("Location: view_post.php?id=" . $id);
        exit();
    }
}

// Get total likes
$likes_count = isset($post['likes_count']) ? $post['likes_count'] : 0;

// Increment view count
if($post['approved'] == 1) {
    $stmt = $pdo->prepare("UPDATE posts SET view_count = view_count + 1 WHERE id = ?");
    $stmt->execute([$id]);
}

// ==================== COMMENT SYSTEM (LOGIN REQUIRED) ====================
// Handle comment submission - ONLY FOR LOGGED IN USERS
$comment_error = '';
$comment_success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_comment'])) {
    // Check if user is logged in
    if(!$is_logged_in) {
        $comment_error = "Please login to comment!";
    } else {
        $comment_text = trim($_POST['comment_text']);
        
        // Validation
        if(empty($comment_text)) {
            $comment_error = "Comment text is required!";
        } elseif(strlen($comment_text) < 3) {
            $comment_error = "Comment must be at least 3 characters long!";
        } else {
            try {
                // Use logged in user's name and email
                $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, commenter_name, commenter_email, comment_text, status) 
                                       VALUES (?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([
                    $id, 
                    $current_user_id, 
                    $_SESSION['full_name'], 
                    $_SESSION['email'], 
                    $comment_text
                ]);
                $comment_success = "Your comment has been submitted for approval!";
                
                // Clear form data
                $_POST = array();
            } catch(PDOException $e) {
                $comment_error = "Error submitting comment: " . $e->getMessage();
            }
        }
    }
}

// Get approved comments
$stmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at DESC");
$stmt->execute([$id]);
$comments = $stmt->fetchAll();

// Get total comments count
$total_comments = count($comments);

// ==================== ALSO LIKE (Similar Posts) - FIXED ====================
$also_like = [];
if($post['approved'] == 1) {
    // Get posts from same category with hardcoded LIMIT
    $stmt = $pdo->prepare("SELECT * FROM posts 
                          WHERE category = ? 
                          AND approved = 1 
                          AND status = 'published' 
                          AND id != ? 
                          ORDER BY created_at DESC 
                          LIMIT 4");
    $stmt->execute([$post['category'], $id]);
    $also_like = $stmt->fetchAll();
    
    // If not enough posts from same category, get latest posts
    if(count($also_like) < 4) {
        $remaining = 4 - count($also_like);
        // Use hardcoded LIMIT with string concatenation (MariaDB fix)
        $query = "SELECT * FROM posts 
                  WHERE approved = 1 
                  AND status = 'published' 
                  AND id != ? 
                  AND category != ? 
                  ORDER BY created_at DESC 
                  LIMIT " . (int)$remaining;
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id, $post['category']]);
        $more_posts = $stmt->fetchAll();
        $also_like = array_merge($also_like, $more_posts);
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Pending Message -->
            <?php if($pending_message): ?>
                <?= $pending_message ?>
            <?php endif; ?>
            
            <article class="bg-white p-4 shadow-sm rounded">
                <div class="mb-3">
                    <span class="badge bg-danger"><?= htmlspecialchars($post['category']) ?></span>
                    <?php if($post['is_latest']): ?>
                        <span class="badge bg-warning text-dark"><i class="fas fa-star"></i> Latest</span>
                    <?php endif; ?>
                    <?php if($post['approved'] == 0): ?>
                        <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pending</span>
                    <?php endif; ?>
                </div>
                
                <h1 class="fw-bold mb-3"><?= htmlspecialchars($post['title']) ?></h1>
                
                <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    <?php if($post['author_image']): ?>
                        <img src="<?= htmlspecialchars($post['author_image']) ?>" class="rounded-circle me-2" style="width: 50px; height: 50px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 50px; height: 50px;">
                            <i class="fas fa-user fa-lg"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <span class="fw-bold"><?= htmlspecialchars($post['author_name']) ?></span><br>
                        <small class="text-muted">
                            <i class="far fa-calendar-alt"></i> <?= date('d M Y, h:i A', strtotime($post['created_at'])) ?>
                            <?php if($post['view_count'] > 0): ?>
                                | <i class="fas fa-eye"></i> <?= $post['view_count'] ?> views
                            <?php endif; ?>
                        </small>
                    </div>
                </div>

                <?php if($post['main_image']): ?>
                    <img src="<?= getPostImage($post) ?>" class="img-fluid w-100 post-img-top mb-4 rounded" alt="Main Image" onerror="this.onerror=null; this.src='uploads/default.jpg'">
                <?php endif; ?>
                
                <div class="content-body fs-5 lh-lg" style="text-align: justify;">
                    <?= nl2br(htmlspecialchars($post['content'])) ?>
                </div>
                
                <!-- ==================== LIKE & SHARE SECTION ==================== -->
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="d-flex align-items-center gap-3">
                            <!-- Like Button - Only for Logged In Users -->
                            <?php if($is_logged_in): ?>
                                <div>
                                    <a href="?action=like&post_id=<?= $id ?>" class="btn <?= $user_liked ? 'btn-danger' : 'btn-outline-danger' ?> like-btn" 
                                       onclick="event.preventDefault(); toggleLike(<?= $id ?>);">
                                        <i class="fas fa-heart"></i> 
                                        <span id="like-count"><?= $likes_count ?></span>
                                        <span class="like-text"><?= $user_liked ? 'Liked' : 'Like' ?></span>
                                    </a>
                                </div>
                            <?php else: ?>
                                <!-- Show Like Button with Login Prompt -->
                                <div>
                                    <a href="login.php" class="btn btn-outline-secondary like-btn" 
                                       data-bs-toggle="tooltip" title="Please login to like this post">
                                        <i class="fas fa-heart"></i> 
                                        <span id="like-count"><?= $likes_count ?></span>
                                        <span>Like</span>
                                    </a>
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-info-circle"></i> 
                                        <a href="login.php" class="text-decoration-none">Login</a> to like
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Comment Count -->
                            <div>
                                <span class="btn btn-outline-secondary">
                                    <i class="fas fa-comment"></i> <?= $total_comments ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Share Buttons -->
                        <div>
                            <span class="text-muted me-2">Share:</span>
                            <a href="#" class="btn btn-sm btn-outline-primary me-1" onclick="shareOnFacebook()">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-info me-1" onclick="shareOnTwitter()">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-success me-1" onclick="shareOnWhatsApp()">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-secondary" onclick="copyLink()">
                                <i class="fas fa-link"></i>
                            </a>
                            <a href="index.php" class="btn btn-outline-dark btn-sm ms-2">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </article>
            
            <!-- ==================== ALSO LIKE SECTION ==================== -->
            <?php if(count($also_like) > 0): ?>
            <section class="mt-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-thumbs-up text-primary"></i> You May Also Like
                </h5>
                <div class="row g-3">
                    <?php foreach($also_like as $similar): ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <a href="view_post.php?id=<?= $similar['id'] ?>" class="text-decoration-none text-dark">
                                    <img src="<?= getPostImage($similar) ?>" class="card-img-top" style="height: 120px; object-fit: cover;" onerror="this.onerror=null; this.src='uploads/default.jpg'">
                                    <div class="card-body p-2">
                                        <h6 class="card-title fw-bold small mb-1 text-truncate-2">
                                            <?= htmlspecialchars(mb_strimwidth($similar['title'], 0, 50, '...')) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt"></i> <?= date('d M Y', strtotime($similar['created_at'])) ?>
                                        </small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
            
            <!-- ==================== COMMENTS SECTION ==================== -->
            <section class="mt-4 bg-white p-4 shadow-sm rounded">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-comments"></i> Comments (<?= $total_comments ?>)
                </h5>
                
                <!-- Display Comments -->
                <?php if($total_comments > 0): ?>
                    <?php foreach($comments as $comment): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold mb-1">
                                        <?= htmlspecialchars($comment['commenter_name']) ?>
                                        <?php if($comment['user_id']): ?>
                                            <span class="badge bg-info text-white">Member</span>
                                        <?php endif; ?>
                                        <?php if($is_admin): ?>
                                            <span class="badge bg-danger">Admin</span>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="far fa-calendar-alt"></i> <?= date('d M Y, h:i A', strtotime($comment['created_at'])) ?>
                                    </small>
                                </div>
                                <?php if($is_admin): ?>
                                    <div>
                                        <a href="admin_comments.php?action=delete&id=<?= $comment['id'] ?>" 
                                           class="text-danger" 
                                           onclick="return confirm('Delete this comment?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted mb-0">No comments yet. Be the first to comment!</p>
                <?php endif; ?>
                
                <!-- ==================== COMMENT FORM (LOGIN REQUIRED) ==================== -->
                <?php if($post['approved'] == 1): ?>
                    <div class="mt-4 pt-3 border-top">
                        <?php if($is_logged_in): ?>
                            <!-- Logged In User - Show Comment Form -->
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-pen"></i> Leave a Comment
                            </h6>
                            
                            <?php if($comment_success): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <i class="fas fa-check-circle"></i> <?= $comment_success ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($comment_error): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <i class="fas fa-exclamation-circle"></i> <?= $comment_error ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="commentForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="text" name="commenter_name" class="form-control" 
                                               placeholder="Your Name" 
                                               value="<?= isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : '' ?>" 
                                               readonly disabled>
                                        <small class="text-muted">Using your registered name</small>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="email" name="commenter_email" class="form-control" 
                                               placeholder="Your Email" 
                                               value="<?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '' ?>"
                                               readonly disabled>
                                        <small class="text-muted">Using your registered email</small>
                                    </div>
                                    <div class="col-12">
                                        <textarea name="comment_text" class="form-control" rows="4" 
                                                  placeholder="Write your comment here..." required><?= isset($_POST['comment_text']) ? htmlspecialchars($_POST['comment_text']) : '' ?></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" name="submit_comment" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Submit Comment
                                        </button>
                                        <small class="text-muted ms-2">
                                            <i class="fas fa-info-circle"></i> 
                                            Your comment will be reviewed before publishing
                                        </small>
                                    </div>
                                </div>
                            </form>
                        <?php else: ?>
                            <!-- Not Logged In - Show Login Prompt -->
                            <div class="text-center py-4">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                    <h6>Please Login to Comment</h6>
                                    <p class="mb-0">You need to be logged in to leave a comment.</p>
                                    <div class="mt-3">
                                        <a href="login.php" class="btn btn-primary">
                                            <i class="fas fa-sign-in-alt"></i> Login Now
                                        </a>
                                        <a href="register.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-user-plus"></i> Create Account
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="mt-4 pt-3 border-top">
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-lock"></i> Comments are disabled for pending posts.
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<!-- ==================== JAVASCRIPT ==================== -->
<script>
// Like toggle function with AJAX (Only for logged in users)
<?php if($is_logged_in): ?>
function toggleLike(postId) {
    const likeBtn = document.querySelector('.like-btn');
    const likeCount = document.getElementById('like-count');
    const likeText = document.querySelector('.like-text');
    
    // Disable button to prevent double clicks
    likeBtn.style.pointerEvents = 'none';
    likeBtn.style.opacity = '0.7';
    
    // Get current action
    const currentAction = likeText.innerText.trim() === 'Liked' ? 'unlike' : 'like';
    
    // Send AJAX request
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'like_handler.php?post_id=' + postId + '&action=' + currentAction, true);
    xhr.onload = function() {
        if (this.status == 200) {
            try {
                const response = JSON.parse(this.responseText);
                if (response.success) {
                    // Update like count
                    likeCount.innerText = response.likes_count;
                    
                    // Toggle button state
                    if (response.action === 'liked') {
                        likeBtn.classList.remove('btn-outline-danger');
                        likeBtn.classList.add('btn-danger');
                        likeText.innerText = 'Liked';
                    } else {
                        likeBtn.classList.remove('btn-danger');
                        likeBtn.classList.add('btn-outline-danger');
                        likeText.innerText = 'Like';
                    }
                }
            } catch(e) {
                // If AJAX fails, reload page as fallback
                window.location.href = 'view_post.php?id=' + postId + '&action=like';
            }
        }
        // Re-enable button
        likeBtn.style.pointerEvents = 'auto';
        likeBtn.style.opacity = '1';
    };
    xhr.onerror = function() {
        // If AJAX fails, reload page as fallback
        window.location.href = 'view_post.php?id=' + postId + '&action=like';
    };
    xhr.send();
}
<?php endif; ?>

// Share functions
function shareOnFacebook() {
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text=<?= urlencode($post['title']) ?>', '_blank', 'width=600,height=400');
}

function shareOnWhatsApp() {
    window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent('<?= addslashes($post['title']) ?> - ' + window.location.href), '_blank', 'width=600,height=400');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href);
    alert('Link copied to clipboard!');
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.post-img-top {
    max-height: 500px;
    object-fit: cover;
}
.content-body {
    font-family: 'Inter', sans-serif;
}
.content-body p {
    margin-bottom: 1.2rem;
}
.like-btn {
    transition: all 0.3s ease;
    min-width: 100px;
}
.like-btn:hover {
    transform: scale(1.05);
}
.like-btn .fa-heart {
    animation: heartBeat 0.3s ease;
}
@keyframes heartBeat {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}
</style>

<?php include 'footer.php'; ?>