<style>
    .sidebar {
        min-height: 100vh !important;
        background: #1e88e5;
        color: white;
    }

    .sidebar .nav-link {
        color: rgba(255, 255, 255, .8) !important;
        padding: 1rem;
    }

    .sidebar .nav-link:hover {
        color: white !important;
        background: rgba(255, 255, 255, .1) !important;
    }

    .sidebar .nav-link.active {
        color: white !important;
        background: rgba(255, 255, 255, .2) !important;
    }
</style>
<?php
$books_enabled = false;
$current_page = basename($_SERVER['PHP_SELF']);
$stats = [
    'users' => mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM users"))['count'],
    // 'books' => mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM book_listings"))['count'],
    'files' => mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM digital_files"))['count'],
    'reports' => mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM reported_content WHERE status = 'pending'"))['count']
];
?>
<div class="col-md-2 px-0 sidebar">
    <div class="p-3 text-center">
        <i class="fas fa-user-shield fa-3x mb-2"></i>
        <h5>Admin Panel</h5>
    </div>
    <div class="nav flex-column">
        <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="users.php" class="nav-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
            <i class="fas fa-users me-2"></i>Users
        </a>
        <?php if ($books_enabled): ?>
            <a href="books.php" class="nav-link <?php echo ($current_page == 'books.php') ? 'active' : ''; ?>">
                <i class="fas fa-book me-2"></i>Books
            </a>
        <?php endif; ?>
        <a href="educational_metadata.php"
            class="nav-link <?php echo ($current_page == 'educational_metadata.php') ? 'active' : ''; ?>">
            <i class="fas fa-book me-2"></i>Educational Metadata
        </a>
        <a href="files.php" class="nav-link <?php echo ($current_page == 'files.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-alt me-2"></i>Files
        </a>
        <a href="reports.php" class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
            <i class="fas fa-flag me-2"></i>Reports
            <?php if ($stats['reports'] > 0): ?>
                <span class="badge bg-danger float-end"><?php echo $stats['reports']; ?></span>
            <?php endif; ?>
        </a>
        <a href="settings.php" class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
            <i class="fas fa-cog me-2"></i>Settings
        </a>
        <a href="../logout.php" class="nav-link">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </div>
</div>