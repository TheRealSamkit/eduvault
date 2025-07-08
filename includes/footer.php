<?php if (!str_contains($_SERVER['PHP_SELF'], 'login') && !str_contains($_SERVER['PHP_SELF'], 'register')): ?>
    <?php if (isLoggedIn()): ?>
        <div class="mt-sm-5 mt-lg-0"></div>
        <nav class="navbar fixed-bottom d-md-none mobile-nav">
            <div class="container-fluid d-flex justify-content-around text-center py-1">
                <a href="/eduvault/dashboard/dashboard.php" class="text-decoration-none nav-link p-1">
                    <i class="fas fa-home fa-lg d-block"></i>
                </a>
                <a href="/eduvault/files/list.php" class="text-decoration-none nav-link p-1">
                    <i class="fas fa-file fa-lg d-block"></i>
                </a>
                <a href="/eduvault/files/upload.php" class="text-decoration-none nav-link p-1">
                    <i class="fas fa-plus-circle fa-lg d-block"></i>
                </a>
                <a href="" class="text-decoration-none nav-link p-1 position-relative ">
                    <i class="fas fa-bell fa-lg d-block"></i>
                    <span
                        class="position-absolute top-0 start-100 translate-middle badge border border-1 border-light rounded-circle bg-danger p-1"><span
                            class="visually-hidden">unread messages</span></span>
                </a>
                <a href="/eduvault/pages/view.php?id=<?= $_SESSION['user_id'] ?>" class="text-decoration-none nav-link p-1">
                    <i class="fas fa-user fa-lg d-block"></i>
                </a>
            </div>
        </nav>
    <?php endif; ?>
    <footer class="bg-dark text-light py-5 mt-auto d-none d-md-block">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-primary mb-3">
                        EduVault
                    </h5>
                    <p class="text-muted mb-3">
                        India's unified platform for students to donate, exchange physical books, and share digital study
                        resources. Building a community of learners supporting each other.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-muted text-decoration-none hover-primary">
                            <i class="fab fa-facebook fs-5"></i>
                        </a>
                        <a href="#" class="text-muted text-decoration-none hover-primary">
                            <i class="fab fa-twitter fs-5"></i>
                        </a>
                        <a href="#" class="text-muted text-decoration-none hover-primary">
                            <i class="fab fa-instagram fs-5"></i>
                        </a>
                        <a href="#" class="text-muted text-decoration-none hover-primary">
                            <i class="fab fa-linkedin fs-5"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-primary mb-3">Platform</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="/eduvault/books/list.php" class="text-muted text-decoration-none hover-primary">
                                Browse Books
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="/eduvault/files/list.php" class="text-muted text-decoration-none hover-primary">
                                Digital Notes
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="/eduvault/books/add.php" class="text-muted text-decoration-none hover-primary">
                                Donate Books
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="/eduvault/files/upload.php" class="text-muted text-decoration-none hover-primary">
                                Upload Notes
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-primary mb-3">Resources</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="#" class="text-muted text-decoration-none hover-primary">
                                CBSE Materials
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="http://localhost/eduvault/books/list.php?board=5"
                                class="text-muted text-decoration-none hover-primary">
                                State Board
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-muted text-decoration-none hover-primary">
                                Engineering Notes
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-muted text-decoration-none hover-primary">
                                UPSC Materials
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-primary mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="#" class="text-muted text-decoration-none hover-primary">
                                Help Center
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="/eduvault/index.php#how_it_works" class=" text-muted text-decoration-none
                                hover-primary">
                                How to Use
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-muted text-decoration-none hover-primary">
                                Community Rules
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-muted text-decoration-none hover-primary">
                                Contact Us
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-primary mb-3">Connect</h6>
                    <div class="text-muted">
                        <div class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <small>support@eduvault.in</small>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <small>India</small>
                        </div>
                        <div class="mb-3">
                            <i class="fas fa-clock me-2"></i>
                            <small>24/7 Community Support</small>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-secondary my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        <small>&copy; <?php echo date('Y'); ?> EduVault. Made with <i class="fas fa-heart text-danger"></i>
                            for Indian Students</small>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        <a href="/eduvault/pages/privacy.php" class="text-decoration-none hover-primary me-3">Privacy
                            Policy</a>
                        <a href="/eduvault/pages/terms.php" class="text-decoration-none hover-primary me-3">Terms of
                            Service</a>
                        <a href="#" class="text-decoration-none hover-primary">Sitemap</a>
                    </small>
                </div>
            </div>

            <button id="scrollToTopBtn" class="btn btn-primary position-fixed end-0 m-3 rounded-circle d-none"
                onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
                style="width: 50px; height: 50px; z-index: 1000;bottom:40px;">
                <i class="fas fa-arrow-up"></i>
            </button>

        </div>
    </footer>

    <?php
endif;
if (isset($additionalScripts)) {
    foreach ($additionalScripts as $script) {
        echo "<script src='/eduvault/assets/js/$script'></script>";
    }
}
?>
<script src="/eduvault/assets/js/main.js"></script>
<script src="/eduvault/assets/js/bootstrap.js"></script>

</body>

</html>