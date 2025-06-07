<?php
require_once 'includes/db_connect.php';
require_once 'includes/session.php';
require_once 'includes/header.php';

// Get some statistics
$books_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM book_listings WHERE status = 'Available'"))['count'];
$files_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM digital_files"))['count'];
$users_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM users"))['count'];
?>

<style>
.hero-section {
    background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
    color: white;
    padding: 80px 0;
    margin-top: -24px;
}

.feature-card {
    border: none;
    transition: transform 0.3s;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-icon {
    width: 60px;
    height: 60px;
    background: #1e88e5;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #1e88e5;
}
</style>

<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="display-4 mb-4">Welcome to EduVault</h1>
                <p class="lead mb-4">Your one-stop platform for sharing and accessing educational resources. Connect with fellow students, share books, and access digital study materials.</p>
                <?php if (!isLoggedIn()): ?>
                    <div class="d-flex gap-3">
                        <a href="register.php" class="btn btn-light btn-lg">Get Started</a>
                        <a href="login.php" class="btn btn-outline-light btn-lg">Sign In</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-5">
                <img src="assets/images/hero-image.png" alt="Education Illustration" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <div class="stat-card">
                <div class="stat-number"><?php echo $books_count; ?></div>
                <div class="text-muted">Available Books</div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="stat-card">
                <div class="stat-number"><?php echo $files_count; ?></div>
                <div class="text-muted">Study Materials</div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="stat-card">
                <div class="stat-number"><?php echo $users_count; ?></div>
                <div class="text-muted">Active Users</div>
            </div>
        </div>
    </div>

    <h2 class="text-center mb-5">Why Choose EduVault?</h2>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon">
                        <i class="fas fa-book fa-2x"></i>
                    </div>
                    <h4>Book Sharing</h4>
                    <p>Share and borrow books with students in your area. Save money and help others learn.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon">
                        <i class="fas fa-file-alt fa-2x"></i>
                    </div>
                    <h4>Digital Resources</h4>
                    <p>Access and share digital study materials, notes, and educational content.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h4>Community</h4>
                    <p>Connect with fellow students, share knowledge, and grow together.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <h2 class="mb-4">Ready to Get Started?</h2>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-primary btn-lg">Join EduVault Today</a>
        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-md-4 mb-3">
                    <a href="books/list.php" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-book me-2"></i>Browse Books
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="files/list.php" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-file-alt me-2"></i>Study Materials
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>