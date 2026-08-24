<?php
include 'header.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if(empty($slug)) {
    header("Location: index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$category = $stmt->fetch();

if(!$category) {
    ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                    <h3 class="fw-bold">Category Not Found</h3>
                    <p class="text-muted">The category you're looking for doesn't exist.</p>
                    <a href="index.php" class="btn btn-primary-custom mt-3">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
    include 'footer.php';
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM posts WHERE category = ? AND approved = 1 AND status = 'published' ORDER BY created_at DESC");
$stmt->execute([$category['name']]);
$posts = $stmt->fetchAll();

$total_posts = count($posts);
$recent_posts = $pdo->query("SELECT * FROM posts WHERE approved = 1 AND status = 'published' ORDER BY created_at DESC LIMIT 5");
?>

<!-- CATEGORY HEADER -->
<section class="category-header mb-4">
    <div class="container">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4" style="background: linear-gradient(135deg, <?= $category['color'] ?> 0%, <?= $category['color'] ?>cc 100%); color: white;">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <i class="<?= $category['icon_class'] ?> fa-3x"></i>
                    </div>
                    <div>
                        <h1 class="fw-bold mb-1"><?= htmlspecialchars($category['name']) ?></h1>
                        <p class="mb-0 opacity-75"><?= htmlspecialchars($category['description'] ?? 'Browse all articles in this category') ?></p>
                        <small class="opacity-75"><i class="fas fa-newspaper"></i> <?= $total_posts ?> articles</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORY CONTENT -->
<section class="category-content mb-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if($total_posts > 0): ?>
                    <div class="row g-4">
                        <?php foreach($posts as $post): ?>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <img src="<?= getPostImage($post) ?>" 
                                         class="card-img-top" style="height: 200px; object-fit: cover;" 
                                         alt="Post" onerror="this.onerror=null; this.src='uploads/default.jpg'">
                                    <div class="card-body">
                                        <span class="badge bg-primary mb-2"><?= htmlspecialchars($post['category']) ?></span>
                                        <?php if($post['is_latest']): ?>
                                            <span class="badge bg-danger mb-2">Latest</span>
                                        <?php endif; ?>
                                        <h5 class="card-title fw-bold">
                                            <a href="view_post.php?id=<?= $post['id'] ?>" class="text-dark text-decoration-none">
                                                <?= htmlspecialchars(mb_strimwidth($post['title'], 0, 60, '...')) ?>
                                            </a>
                                        </h5>
                                        <p class="card-text small text-muted">
                                            <?= htmlspecialchars(mb_strimwidth(strip_tags($post['content'] ?? ''), 0, 100, '...')) ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><i class="fas fa-user"></i> <?= htmlspecialchars($post['author_name']) ?></small>
                                            <small class="text-muted"><i class="far fa-calendar-alt"></i> <?= date('M d, Y', strtotime($post['created_at'])) ?></small>
                                        </div>
                                        <div class="mt-2">
                                            <a href="view_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                Read More <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                        <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
                        <h4 class="fw-bold">No Articles Found</h4>
                        <p class="text-muted">There are no published articles in this category yet.</p>
                        <a href="index.php" class="btn btn-primary-custom">
                            <i class="fas fa-arrow-left"></i> Back to Home
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <!-- Recent Posts -->
                <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
                    <h5 class="fw-bold border-bottom pb-2"><i class="fas fa-clock text-primary"></i> Recent Posts</h5>
                    <?php while($recent = $recent_posts->fetch()): ?>
                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                            <img src="<?= getPostImage($recent) ?>" 
                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;" 
                                 alt="Recent" onerror="this.onerror=null; this.src='uploads/default.jpg'">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1" style="font-size: 0.85rem;">
                                    <a href="view_post.php?id=<?= $recent['id'] ?>" class="text-dark text-decoration-none">
                                        <?= htmlspecialchars(mb_strimwidth($recent['title'], 0, 40, '...')) ?>
                                    </a>
                                </h6>
                                <small class="text-muted"><i class="far fa-calendar-alt"></i> <?= date('M d, Y', strtotime($recent['created_at'])) ?></small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- All Categories -->
                <div class="card border-0 shadow-sm rounded-4 p-3">
                    <h5 class="fw-bold border-bottom pb-2"><i class="fas fa-folder-open text-primary"></i> All Categories</h5>
                    <?php 
                    $all_cats = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
                    while($cat = $all_cats->fetch()): 
                    ?>
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
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>