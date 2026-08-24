<?php
include 'header.php';

// Fetch data
$featured = $pdo->query("SELECT * FROM posts WHERE is_featured = 1 AND approved = 1 AND status = 'published' ORDER BY created_at DESC LIMIT 1")->fetch();

if(!$featured) {
    $featured = $pdo->query("SELECT * FROM posts WHERE approved = 1 AND status = 'published' ORDER BY created_at DESC LIMIT 1")->fetch();
}

$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1");
$has_posts = $pdo->query("SELECT COUNT(*) FROM posts WHERE approved = 1 AND status = 'published'")->fetchColumn();
?>

<?php if($has_posts > 0): ?>

<!-- HERO SECTION -->
<section class="hero-section mb-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if($featured): ?>
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                    <img src="<?= getPostImage($featured) ?>" 
                         class="w-100" style="height: 450px; object-fit: cover;" alt="Featured"
                         onerror="this.onerror=null; this.src='uploads/default.jpg'">
                    <div class="position-absolute bottom-0 start-0 w-100 p-4" 
                         style="background: linear-gradient(transparent, rgba(0,0,0,0.8));">
                        <span class="badge bg-danger mb-2"><i class="fas fa-star"></i> Featured</span>
                        <h2 class="text-white fw-bold">
                            <a href="view_post.php?id=<?= $featured['id'] ?>" class="text-white text-decoration-none">
                                <?= htmlspecialchars($featured['title']) ?>
                            </a>
                        </h2>
                        <p class="text-white-50 mb-0"><?= htmlspecialchars(substr(strip_tags($featured['content'] ?? ''), 0, 150)) ?>...</p>
                        <div class="mt-2">
                            <small class="text-white-50"><i class="fas fa-user"></i> <?= htmlspecialchars($featured['author_name']) ?></small>
                            <small class="text-white-50 ms-3"><i class="far fa-calendar-alt"></i> <?= date('M d, Y', strtotime($featured['created_at'])) ?></small>
                            <small class="text-white-50 ms-3"><i class="fas fa-eye"></i> <?= number_format($featured['view_count'] ?? 0) ?></small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <h5 class="fw-bold border-bottom pb-2"><i class="fas fa-clock text-primary"></i> Latest News</h5>
                    <?php 
                    $latest_sidebar = $pdo->query("SELECT * FROM posts WHERE approved = 1 AND status = 'published' ORDER BY created_at DESC LIMIT 5");
                    while($news = $latest_sidebar->fetch()): 
                    ?>
                    <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                        <img src="<?= getPostImage($news) ?>" 
                             style="width: 70px; height: 70px; object-fit: cover; border-radius: 10px;" 
                             alt="News" onerror="this.onerror=null; this.src='uploads/default.jpg'">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">
                                <a href="view_post.php?id=<?= $news['id'] ?>" class="text-dark text-decoration-none">
                                    <?= htmlspecialchars(mb_strimwidth($news['title'], 0, 50, '...')) ?>
                                </a>
                            </h6>
                            <small class="text-muted"><i class="far fa-calendar-alt"></i> <?= date('M d, Y', strtotime($news['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORIES -->
<?php if($categories->rowCount() > 0): ?>
<section class="categories-section mb-5">
    <div class="container">
        <h4 class="fw-bold mb-3"><i class="fas fa-folder-open text-primary"></i> Browse Categories</h4>
        <div class="d-flex flex-wrap gap-2">
            <?php while($cat = $categories->fetch()): ?>
                <a href="category.php?slug=<?= $cat['slug'] ?>" 
                   class="btn btn-outline-primary rounded-pill px-4 py-2" 
                   style="border-color: <?= $cat['color'] ?>; color: <?= $cat['color'] ?>;">
                    <i class="<?= $cat['icon_class'] ?>"></i> <?= $cat['name'] ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- LATEST ARTICLES -->
<section class="latest-posts mb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold"><i class="fas fa-newspaper text-success"></i> Latest Articles</h4>
            <a href="#" class="text-decoration-none">View All →</a>
        </div>
        <div class="row g-4">
            <?php 
            $posts = $pdo->query("SELECT * FROM posts WHERE approved = 1 AND status = 'published' ORDER BY created_at DESC LIMIT 8");
            while($post = $posts->fetch()): 
            ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                        <img src="<?= getPostImage($post) ?>" 
                             class="card-img-top" style="height: 200px; object-fit: cover;" 
                             alt="Post" onerror="this.onerror=null; this.src='uploads/default.jpg'">
                        <div class="card-body">
                            <span class="badge bg-primary mb-2"><?= htmlspecialchars($post['category']) ?></span>
                            <h6 class="card-title fw-bold">
                                <a href="view_post.php?id=<?= $post['id'] ?>" class="text-dark text-decoration-none">
                                    <?= htmlspecialchars(mb_strimwidth($post['title'], 0, 50, '...')) ?>
                                </a>
                            </h6>
                            <p class="card-text small text-muted"><?= htmlspecialchars(mb_strimwidth(strip_tags($post['content'] ?? ''), 0, 80, '...')) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><i class="fas fa-user"></i> <?= htmlspecialchars($post['author_name']) ?></small>
                                <small class="text-muted"><i class="fas fa-eye"></i> <?= number_format($post['view_count'] ?? 0) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php else: ?>

<!-- NO POSTS -->
<section class="no-posts-section py-5">
    <div class="container">
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <i class="fas fa-newspaper fa-5x text-muted mb-4"></i>
            <h3 class="fw-bold">No Posts Available</h3>
            <p class="text-muted">There are no published posts yet. Check back soon for exciting content!</p>
        </div>
    </div>
</section>

<?php endif; ?>

<?php include 'footer.php'; ?>