<style>
    .sidebar {
        min-height: 100vh;
        background: #1e88e5;
        color: white;
    }

    .sidebar .nav-link {
        color: rgba(255, 255, 255, .8);
        padding: 1rem;
    }

    .sidebar .nav-link:hover {
        color: white;
        background: rgba(255, 255, 255, .1);
    }

    .sidebar .nav-link.active {
        color: white;
        background: rgba(255, 255, 255, .2);
    }
</style>
<?php
$stats = [
    'users' => mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM users"))['count'],
    'books' => mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM book_listings"))['count'],
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
        <a href="dashboard.php" class="nav-link active">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="users.php" class="nav-link">
            <i class="fas fa-users me-2"></i>Users
        </a>
        <a href="books.php" class="nav-link">
            <i class="fas fa-book me-2"></i>Books
        </a>
        <a href="files.php" class="nav-link">
            <i class="fas fa-file-alt me-2"></i>Files
        </a>
        <a href="reports.php" class="nav-link">
            <i class="fas fa-flag me-2"></i>Reports
            <?php if ($stats['reports'] > 0): ?>
                <span class="badge bg-danger float-end"><?php echo $stats['reports']; ?></span>
            <?php endif; ?>
        </a>
        <a href="settings.php" class="nav-link">
            <i class="fas fa-cog me-2"></i>Settings
        </a>
        <a href="../logout.php" class="nav-link">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </div>
</div>