</main>

<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a href="index.php" class="brand">
                    <i class="fas fa-newspaper"></i> News<span>Verse</span>
                </a>
                <p class="mt-3">Smart Digital Media Knowledge Platform. Delivering quality news and insights to our readers worldwide.</p>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <h6 class="text-white fw-bold mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-4">
                <h6 class="text-white fw-bold mb-3">Categories</h6>
                <ul class="list-unstyled">
                    <?php 
                    $footer_cats = $pdo->query("SELECT * FROM categories WHERE is_active = 1 LIMIT 5");
                    while($fc = $footer_cats->fetch()): 
                    ?>
                    <li><a href="category.php?slug=<?= $fc['slug'] ?>"><?= $fc['name'] ?></a></li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <div class="col-lg-4 col-md-4">
                <h6 class="text-white fw-bold mb-3">Subscribe</h6>
                <p>Get the latest news delivered to your inbox</p>
                <form>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email" style="border-radius: 50px 0 0 50px;">
                        <button class="btn btn-warning" style="border-radius: 0 50px 50px 0;">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <div class="text-center small">
            &copy; <?= date('Y') ?> NewsVerse. All rights reserved. | Made with <i class="fas fa-heart text-danger"></i>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>