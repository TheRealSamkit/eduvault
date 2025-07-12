<div class="dashboard-sidebar d-lg-block position-lg-sticky top-0" id="dashboardSidebar"
    style="background: var(--bg-body-tertiary); border-right: 1px solid var(--bs-border-color); min-height: 100vh;">
    <div class="sidebar-header text-center py-4 border-bottom mb-2 rounded-4" style="background: var(--bg-subtle);">
        <?php if (isset($_SESSION['user_id'])): ?>
            <img src="<?php echo htmlspecialchars($_SESSION['avatar'] ?? '/eduvault/assets/images/404.png'); ?>"
                class="rounded-circle mb-2" width="56" height="56" alt="Avatar">
            <div class="fw-bold" style="color: var(--bs-body-color);">
                <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>
            </div>
        <?php else: ?>
            <div class="fw-bold" style="color: var(--bs-body-color);">EduVault</div>
        <?php endif; ?>
    </div>
    <nav class="nav flex-column mt-2">
        <a href="/eduvault/dashboard/dashboard.php"
            class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt me-2"></i> Overview
        </a>
        <?php if ($books_enabled): ?>
            <a href="/eduvault/dashboard/my_books.php"
                class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'my_books.php') ? 'active' : ''; ?>">
                <i class="fas fa-book me-2"></i> My Books
            </a>
        <?php endif; ?>
        <a href="/eduvault/dashboard/my_uploads.php"
            class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'my_uploads.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-alt me-2"></i> My Uploads
        </a>
        <a href="/eduvault/dashboard/bookmarks.php"
            class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'bookmarks.php') ? 'active' : ''; ?>">
            <i class="fas fa-bookmark me-2"></i> Bookmarks
        </a>
        <a href="/eduvault/files/upload.php"
            class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'upload.php') ? 'active' : ''; ?>">
            <i class="fas fa-upload me-2"></i> Upload File
        </a>
        <?php if ($books_enabled): ?>
            <a href="/eduvault/books/add.php"
                class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'add.php') ? 'active' : ''; ?>">
                <i class="fas fa-plus me-2"></i> Add Book
            </a>
        <?php endif; ?>
        <a href="/eduvault/pages/settings.php"
            class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>">
            <i class="fas fa-cog me-2"></i> Settings
        </a>
        <div class="border-top">
            <a href="/eduvault/logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
        <?php if (isLoggedIn()): ?>
            <div class="border-top"></div>
            <div class="sidebar-section-info pt-3 d-md-none">
                <div class="sidebar-section-title text-uppercase small fw-bold text-muted mb-2 ps-2">More</div>
                <a href="/eduvault/index.php" class="nav-link"><i class="fas fa-info-circle me-2"></i> About</a>
                <a href="/eduvault/pages/privacy.php" class="nav-link"><i class="fas fa-user-secret me-2"></i> Privacy
                    Policy</a>
                <a href="/eduvault/pages/terms.php" class="nav-link"><i class="fas fa-file-contract me-2"></i> Terms of
                    Service</a>
                <a href="/eduvault/pages/view.php?id=<?php echo $_SESSION['user_id']; ?>#contact" class="nav-link"><i
                        class="fas fa-envelope me-2"></i> Contact</a>
                <a href="#" class="nav-link"><i class="fas fa-sitemap me-2"></i> Sitemap</a>
            </div>
        <?php endif; ?>
    </nav>
</div>
<div class="sidebar-backdrop d-lg-none" id="sidebarBackdrop" style="display:none;"></div>