<?php
require_once 'includes/db_connect.php';
$additionalStyles[] = 'index.css';
$additionalScripts[] = 'index.js';
require_once 'includes/session.php';
require_once 'includes/functions.php';
if (isLoggedIn()) {
    header("Location: dashboard/dashboard.php");
    exit();
}
include 'includes/header.php';

$downloads_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM downloads"))['count'];
$books_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM book_listings WHERE status = 'Available'"))['count'];
$files_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM digital_files"))['count'];
$users_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM users where last_active >= now() - Interval 1 hour"))['count'];

// Get recent books and files for featured section
$recent_books = mysqli_query($mysqli, "SELECT b.*,u.name as author FROM book_listings b join users u on b.user_id=u.id WHERE b.status = 'Available' ORDER BY created_at DESC LIMIT 6");
$recent_files = mysqli_query($mysqli, "SELECT * FROM digital_files ORDER BY upload_date DESC LIMIT 6");
?>
<div class="hero-section">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-3 mb-4 fw-bold">Welcome to EduVault</h1>
                <p class="lead mb-4 fs-4">India's unified platform for students to donate, exchange physical books, and
                    share digital study resources. Building a community of learners supporting each other.</p>
                <?php if (!isLoggedIn()): ?>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="register.php" class="btn btn-light btn-lg px-5 py-3">
                            <i class="fas fa-rocket me-2"></i>Get Started
                        </a>
                        <a href="login.php" class="btn btn-outline-light btn-lg px-5 py-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-5 text-center">
                <div class="hero-image">
                    <i class="fas fa-graduation-cap" style="font-size: 15rem; opacity: 0.8;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-number" data-target="<?php echo $books_count; ?>">0</div>
                <div class="text-muted fs-5">Available Books</div>
                <small class="text-success"><i class="fas fa-arrow-up"></i> Growing daily</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-number" data-target="<?php echo $files_count; ?>">0</div>
                <div class="text-muted fs-5">Study Materials</div>
                <small class="text-success"><i class="fas fa-arrow-up"></i> Fresh content</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-number" data-target="<?php echo $users_count; ?>">0</div>
                <div class="text-muted fs-5">Active Users</div>
                <small class="text-primary"><i class="fas fa-users"></i> Online now</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-number" data-target="<?php echo $downloads_count; ?>">0</div>
                <div class="text-muted fs-5">Total Downloads</div>
                <small class="text-info"><i class="fas fa-download"></i> Resources shared</small>
            </div>
        </div>
    </div>
</div>
<div class="bg-dark-body py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 mb-3">Why Choose EduVault?</h2>
                <p class="lead text-muted">Empowering students across India with accessible education resources</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-book fa-2x"></i>
                        </div>
                        <h4 class="mb-3">Book Sharing</h4>
                        <p class="text-muted">Share and borrow books with students in your area. Save money and help
                            others learn while building a sustainable education ecosystem.</p>
                        <a href="books/list.php" class="btn btn-outline-primary mt-3">Explore Books</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                        <h4 class="mb-3">Digital Resources</h4>
                        <p class="text-muted">Access and share digital study materials, notes, and educational content.
                            From CBSE to competitive exams, find everything you need.</p>
                        <a href="files/list.php" class="btn btn-outline-primary mt-3">Browse Materials</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <h4 class="mb-3">Community</h4>
                        <p class="text-muted">Connect with fellow students, share knowledge, and grow together. Join
                            thousands of learners across India.</p>
                        <a href="register.php" class="btn btn-outline-primary mt-3">Join Community</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5" id="how_it_works">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h2 class="display-5 mb-3">How It Works</h2>
            <p class="lead text-muted">Simple steps to start sharing and learning</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="process-step">
                <div class="process-number">1</div>
                <h5>Sign Up</h5>
                <p class="text-muted">Create your free account and join our community of learners</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="process-step">
                <div class="process-number">2</div>
                <h5>Browse Resources</h5>
                <p class="text-muted">Search for books and study materials you need for your studies</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="process-step">
                <div class="process-number">3</div>
                <h5>Share & Connect</h5>
                <p class="text-muted">Donate books or upload digital materials to help others</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="process-step">
                <div class="process-number">4</div>
                <h5>Learn Together</h5>
                <p class="text-muted">Access shared resources and contribute to the learning community</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-dark-body py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 mb-3">Featured Resources</h2>
                <p class="lead text-muted">Recently added books and study materials</p>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12">
                <h4 class="mb-4"><i class="fas fa-book text-primary me-2"></i>Latest Books</h4>
            </div>
            <?php while ($book = mysqli_fetch_assoc($recent_books)): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card resource-card">
                        <img class="card-img-top" src="<?php echo htmlspecialchars($book['image_path']) ?>" alt="book cover"
                            style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($book['title']); ?>
                                <span
                                    class="category-badge bg-success text-white m-2"><?php echo htmlspecialchars($book['status'] ?? 'General'); ?></span>
                            </h6>
                            <p class="text-muted small">by <?php echo htmlspecialchars($book['author']); ?></p>
                            <p class="text-muted small"><i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($book['location']); ?></p>

                            <a href="books/view.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary">View
                                Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="row">
            <div class="col-12">
                <h4 class="mb-4"><i class="fas fa-file-alt text-success me-2"></i>Latest Study Materials</h4>
            </div>
            <?php while ($file = mysqli_fetch_assoc($recent_files)): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card resource-card">
                        <div class="card-body">
                            <h6 class="card-title mt-2"><i
                                    class="fas fa-file-<?php echo getFileIcon($file['file_type']) ?> text-primary mx-1"></i><?php echo htmlspecialchars($file['title']); ?><span
                                    class="category-badge bg-primary text-white m-3"><?php echo htmlspecialchars($file['subject'] ?? 'Study Material'); ?></span>
                            </h6>
                            <p class="text-muted small"><?php echo htmlspecialchars($file['description']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><?php echo htmlspecialchars($file['file_type']) ?>
                                </small>
                                <?php if (isLoggedIn()): ?>
                                    <a href="files/download.php?id=<?php echo $file['id']; ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-download me-1"></i>Download
                                    </a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-warning btn-sm">
                                        <i class="fas fa-lock me-1"></i>Login to Download
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h2 class="display-5 mb-3">What Students Say</h2>
            <p class="lead text-muted">Real experiences from our community members</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="testimonial-card">
                <div class="testimonial-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <p class="mb-3">"EduVault helped me find rare engineering books at no cost. The community is amazing and
                    always ready to help!"</p>
                <h6 class="text-primary">Priya Sharma</h6>
                <small class="text-muted">Engineering Student, Mumbai</small>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="testimonial-card">
                <div class="testimonial-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <p class="mb-3">"I've shared over 50 books through EduVault. It's satisfying to know they're helping
                    other students succeed."</p>
                <h6 class="text-primary">Rajesh Kumar</h6>
                <small class="text-muted">Medical Student, Delhi</small>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="testimonial-card">
                <div class="testimonial-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <p class="mb-3">"The digital resources section is a goldmine for competitive exam preparation. Highly
                    recommended!"</p>
                <h6 class="text-primary">Anjali Patel</h6>
                <small class="text-muted">UPSC Aspirant, Ahmedabad</small>
            </div>
        </div>
    </div>
</div>

<div class="cta-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 mb-4">Ready to Transform Your Learning?</h2>
                <p class="lead mb-5">Join thousands of students who are already sharing knowledge and growing together
                </p>
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-light btn-lg px-5 py-3 me-3">
                        <i class="fas fa-rocket me-2"></i>Join EduVault Today
                    </a>
                    <a href="books/list.php" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="fas fa-search me-2"></i>Browse Resources
                    </a>
                <?php else: ?>
                    <div class="row justify-content-center">
                        <div class="col-md-4 mb-3">
                            <a href="books/list.php" class="btn btn-light btn-lg w-100 py-3">
                                <i class="fas fa-book me-2"></i>Browse Books
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="files/list.php" class="btn btn-outline-light btn-lg w-100 py-3">
                                <i class="fas fa-file-alt me-2"></i>Study Materials
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="dashboard/dashboard.php" class="btn btn-light btn-lg w-100 py-3">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>