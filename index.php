<?php
require_once 'includes/db_connect.php';
// Add swiper for the carousel
$additionalStyles[] = 'index.css';
$additionalStyles[] = 'https://unpkg.com/swiper/swiper-bundle.min.css';
$additionalScripts[] = 'https://unpkg.com/swiper/swiper-bundle.min.js';
$additionalScripts[] = 'index.js';

require_once 'includes/session.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('dashboard/dashboard.php');
}

include 'includes/header.php';

// --- Data Fetching ---
$downloads_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM downloads"))['count'];
$files_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM digital_files"))['count'];
$users_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM users where last_active >= now() - Interval 1 hour"))['count'];
$recent_files_result = mysqli_query($mysqli, "SELECT df.*, s.name as subject FROM digital_files df LEFT JOIN subjects s ON df.subject_id = s.id WHERE df.status = 'active' AND df.visibility = 'public' ORDER BY df.upload_date DESC LIMIT 9");
$host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
?>
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
<style>
    .hero-section {
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=2070&auto=format&fit=crop') no-repeat center center;
        background-size: cover;
        color: #fff;
        min-height: 80vh;
        display: flex;
        align-items: center;
    }

    .hero-search-form {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        padding: 1rem;
        border-radius: 0.75rem;
        margin-top: 2rem;
    }

    .hero-search-form .form-control {
        border-radius: 0.5rem 0 0 0.5rem;
    }

    .hero-search-form .btn {
        border-radius: 0 0.5rem 0.5rem 0;
    }

    .feature-icon {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
    }

    .process-step .process-number {
        border: 2px solid var(--bs-primary);
        color: #fff;
    }

    .resource-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .testimonial-card .testimonial-avatar {
        width: 60px;
        height: 60px;
        background: var(--bs-primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 1rem;
    }

    .testimonial-stars {
        color: #ffc107;
        margin-bottom: 1rem;
    }
</style>

<div class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 mb-3 fw-bold">Ace Your Exams with Shared Notes</h1>
        <p class="lead mb-4 fs-4 col-lg-8 mx-auto">India's top platform for students to find, share, and download free
            study materials for every course.</p>

        <div class="col-lg-8 mx-auto">
            <form action="files/list.php" method="GET" class="hero-search-form">
                <div class="input-group">
                    <input type="search" name="query" class="form-control form-control-lg"
                        placeholder="Search for notes, question papers, guides..." required>
                    <button type="submit" class="btn btn-primary btn-lg px-4"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>

        <?php if (!isLoggedIn()): ?>
            <div class="mt-4">
                <a href="register.php" class="btn btn-light btn-lg px-5 py-3">Join for Free</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="container py-5">
    <div class="row mb-5 justify-content-center text-center">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-number" data-target="<?php echo $files_count; ?>">0</div>
                <div class="text-muted fs-5">Study Materials</div>
                <small class="text-success"><i class="fas fa-arrow-up"></i> Growing daily</small>
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
    <div class="container py-5" id="how_it_works">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 mb-3">Get Started in Minutes</h2>
                <p class="lead text-muted">A simple path to sharing and learning.</p>
            </div>
        </div>

        <div class="row text-center">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="process-step">
                    <div class="process-number"><i class="fas fa-user-plus"></i></div>
                    <h5>Sign Up</h5>
                    <p class="text-muted">Create your free account to join our community.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="process-step">
                    <div class="process-number"><i class="fas fa-search"></i></div>
                    <h5>Find Resources</h5>
                    <p class="text-muted">Search for the exact materials you need.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="process-step">
                    <div class="process-number"><i class="fas fa-upload"></i></div>
                    <h5>Share & Help</h5>
                    <p class="text-muted">Upload your notes to help other students.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="process-step">
                    <div class="process-number"><i class="fas fa-book-reader"></i></div>
                    <h5>Learn Together</h5>
                    <p class="text-muted">Use shared resources to boost your grades.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h2 class="display-5 mb-3">Featured Study Materials</h2>
            <p class="lead text-muted">Freshly uploaded resources from the community.</p>
        </div>
    </div>

    <div class="swiper-container">
        <div class="swiper-wrapper overflow-x-scroll">
            <?php while ($file = mysqli_fetch_assoc($recent_files_result)): ?>
                <div class="swiper-slide h-auto p-2">
                    <div class="card resource-card h-100">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title mt-2">
                                <i class="fas fa-file-<?php echo getFileIcon($file['file_type']) ?> text-primary mx-1"></i>
                                <?php echo htmlspecialchars($file['title']); ?>
                            </h6>
                            <span
                                class="badge bg-primary align-self-start mb-2"><?php echo htmlspecialchars($file['subject'] ?? 'General'); ?></span>
                            <p class="text-muted small flex-grow-1">
                                <?php echo htmlspecialchars(substr($file['description'], 0, 70)) . '...'; ?>
                            </p>
                            <?php if (isLoggedIn()): ?>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <a href="files/download.php?id=<?php echo $file['id']; ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
                                    <a href="files/preview.php?id=<?php echo $file['id']; ?>"
                                        class="btn btn-outline-secondary btn-sm" target="_blank">
                                        <i class="fas fa-eye me-1"></i> Preview
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="mt-3">
                                    <a href="register.php" class="btn btn-primary btn-sm">
                                        <i class="fas fa-sign-in-alt me-1"></i> Login to Download
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</div>


<div class="bg-dark-body py-5">
    <div class="container py-5">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 mb-3">What Our Students Say</h2>
                <p class="lead text-muted">Real experiences from our amazing community members.</p>
            </div>
        </div>

        <div class="row text-center">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-card">
                    <div class="testimonial-avatar"><i class="fas fa-user"></i></div>
                    <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
                    <p class="mb-3">"Thanks to EduVault, I found the exact 'Data Structures' notes I needed for my final
                        year B.Tech exam. It was a lifesaver!"</p>
                    <h6 class="text-primary">Priya S.</h6>
                    <small class="text-muted">Engineering Student, Mumbai</small>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-card">
                    <div class="testimonial-avatar"><i class="fas fa-user"></i></div>
                    <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
                    <p class="mb-3">"The collection of previous year's question papers for NEET is incredible. It helped
                        me structure my preparation perfectly."</p>
                    <h6 class="text-primary">Rajesh K.</h6>
                    <small class="text-muted">Medical Aspirant, Delhi</small>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-card">
                    <div class="testimonial-avatar"><i class="fas fa-user"></i></div>
                    <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
                    <p class="mb-3">"As a UPSC aspirant, the current affairs compilations and history notes are a
                        goldmine. Highly recommended for all civil service candidates."</p>
                    <h6 class="text-primary">Anjali P.</h6>
                    <small class="text-muted">UPSC Aspirant, Ahmedabad</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="cta-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 mb-4">Ready to Transform Your Learning?</h2>
                <p class="lead mb-5">Join thousands of students who are sharing knowledge and growing together.</p>
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-light btn-lg px-5 py-3 me-3">
                        <i class="fas fa-rocket me-2"></i>Join EduVault Today
                    </a>
                    <a href="files/list.php" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="fas fa-search me-2"></i>Browse Resources
                    </a>
                <?php else: ?>
                    <div class="row justify-content-center">
                        <div class="col-md-6 mb-3">
                            <a href="files/list.php" class="btn btn-outline-light btn-lg w-100 py-3">
                                <i class="fas fa-file-alt me-2"></i>Explore Study Materials
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="dashboard/dashboard.php" class="btn btn-light btn-lg w-100 py-3">
                                <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Swiper Carousel
    document.addEventListener('DOMContentLoaded', function () {
        const swiper = new Swiper('.swiper-container', {
            // Optional parameters
            loop: true,
            slidesPerView: 1,
            spaceBetween: 10,
            // Responsive breakpoints
            breakpoints: {
                // when window width is >= 768px
                768: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                // when window width is >= 992px
                992: {
                    slidesPerView: 3,
                    spaceBetween: 30
                }
            },
            // If we need pagination
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>