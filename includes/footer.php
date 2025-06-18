<?php if (!str_contains($_SERVER['PHP_SELF'], 'login') && !str_contains($_SERVER['PHP_SELF'], 'register')): ?>
    <footer class="bg-dark text-light py-5 mt-auto">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-book-reader me-2"></i>EduVault
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

            <button class="btn btn-primary btn-scroll btn-sm position-fixed bottom-0 end-0 m-3 rounded-circle"
                onclick="window.scrollTo({top: 0, behavior: 'smooth'})" style="width: 50px; height: 50px; z-index: 1000;">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>