<?php
require_once 'session.php';
$currentPage = $_SERVER['PHP_SELF'];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EduVault - Educational Resource Sharing</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>assets/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>assets/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>assets/favicon/favicon-16x16.png">
        <link rel="manifest" href="<?= BASE_URL ?>assets/favicon/site.webmanifest">
        <link href="<?= BASE_URL ?>assets/css/bootstrap.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <?php
        if (isset($additionalStyles)) {
            foreach ($additionalStyles as $style) {
                echo "<link rel='stylesheet' href='/eduvault/assets/css/$style'>";
            }
        }
        ?>
        <link rel='stylesheet' href='<?= BASE_URL ?>assets/css/theme.css'>
    </head>

    <body class="bg-dark-body text-body">
        <div class="bg-dark-body d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100 "
            style="z-index: 9999;" id="pageLoader">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h4 class="text-primary">Loading EduVault...</h4>
            </div>
        </div>
        <?php if (!str_contains($_SERVER['PHP_SELF'], 'login') && !str_contains($_SERVER['PHP_SELF'], 'register')): ?>

            <nav class="navbar navbar-expand-lg mb-4 bg-dark-body">
                <div class="container">
                    <a class="navbar-brand" href="/eduvault/index.php">
                        <i class="fas fa-book-reader me-2"></i>EduVault
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <i class="fas fa-bars color fs-2"></i>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link <?php echo str_contains($_SERVER['PHP_SELF'], 'books') ? 'active' : ''; ?>"
                                    href="/eduvault/books/list.php">
                                    <i class="fas fa-book me-1"></i>Books
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo str_contains($_SERVER['PHP_SELF'], 'files') ? 'active' : ''; ?>"
                                    href="/eduvault/files/list.php">
                                    <i class="fas fa-file-alt me-1"></i>Study Materials
                                </a>
                            </li>
                            <?php if (isLoggedIn()): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo str_contains($_SERVER['PHP_SELF'], 'dashboard') ? 'active' : ''; ?>"
                                        href="/eduvault/dashboard/dashboard.php">
                                        <i class="fas fa-user me-1"></i>Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/eduvault/logout.php">
                                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="/eduvault/login.php">
                                        <i class="fas fa-sign-in-alt me-1"></i>Login
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/eduvault/register.php">
                                        <i class="fas fa-user-plus me-1"></i>Register
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item dropdown">
                                <button class="btn nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fas fa-adjust"></i> Theme
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><button type="button" class="dropdown-item d-flex align-items-center"
                                            data-theme-value="light">
                                            <i class="fas fa-sun me-2"></i> Light
                                        </button></li>
                                    <li><button type="button" class="dropdown-item d-flex align-items-center"
                                            data-theme-value="dark">
                                            <i class="fas fa-moon me-2"></i> Dark
                                        </button></li>
                                    <li><button type="button" class="dropdown-item d-flex align-items-center"
                                            data-theme-value="auto">
                                            <i class="fas fa-desktop me-2"></i> System
                                        </button></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        <?php endif; ?>
        <div class="toast-container position-fixed top-0 end-0 p-3">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive"
                    aria-atomic="true" id="errorToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <?= $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive"
                    aria-atomic="true" id="successToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <?= $_SESSION['success'];
                            unset($_SESSION['success']); ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
        </div>