<div class="dashboard-sidebar d-lg-block position-lg-sticky top-0" id="dashboardSidebar">
    <nav class="nav flex-column mt-3">
        <a href="/eduvault/dashboard/dashboard.php"
            class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt me-2"></i> Overview
        </a>
        <a href="/eduvault/dashboard/my_books.php"
            class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'my_books.php') ? 'active' : ''; ?>">
            <i class="fas fa-book me-2"></i> My Books
        </a>
        <a href="/eduvault/dashboard/my_uploads.php"
            class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'my_uploads.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-alt me-2"></i> My Uploads
        </a>
        <a href="/eduvault/books/add.php"
            class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'add.php') ? 'active' : ''; ?>">
            <i class="fas fa-plus me-2"></i> Add Book
        </a>
        <a href="/eduvault/files/upload.php"
            class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'upload.php') ? 'active' : ''; ?>">
            <i class="fas fa-upload me-2"></i> Upload File
        </a>
        <a href="/eduvault/pages/change_password.php"
            class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'change_password.php') ? 'active' : ''; ?>">
            <i class="fas fa-key me-2"></i> Change Password
        </a>
        <a href="/eduvault/logout.php" class="nav-link text-danger">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
        </a>
    </nav>
</div>
<div class="sidebar-backdrop d-lg-none" id="sidebarBackdrop" style="display:none;"></div>