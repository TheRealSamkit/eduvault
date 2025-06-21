<?php
require_once 'session.php';
$currentPage = $_SERVER['PHP_SELF'];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $pageTitle ?? "EduVault - Educational Resource Sharing" ?></title>
        <link rel="apple-touch-icon" sizes="180x180" href="/eduvault/assets/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/eduvault/assets/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/eduvault/assets/favicon/favicon-16x16.png">
        <link href="/eduvault/assets/css/bootstrap.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <?php
        if (isset($additionalStyles)) {
            foreach ($additionalStyles as $style) {
                echo "<link rel='stylesheet' href='/eduvault/assets/css/$style'>";
            }
        }
        ?>
        <link rel='stylesheet' href='/eduvault/assets/css/theme.css'>
        <link rel='stylesheet' href='/eduvault/assets/css/custom.css'>
    </head>

    <body class="bg-dark-body text-body">
        <div class="bg-dark-body d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100 "
            style="z-index: 9999;" id="pageLoader">
            <div class="text-center">
                <div class="spinner-border text-primary fs-2 mb-3 d-flex justify-content-center align-items-center"
                    style="width: 5rem; height: 5rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                    <div class="spinner-border text-success fs-2 d-flex justify-content-center align-items-center"
                        style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                        <div class="spinner-border text-warning fs-6" style="width: 1rem; height: 1rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!str_contains($_SERVER['PHP_SELF'], 'login') && !str_contains($_SERVER['PHP_SELF'], 'register')):
            if (!isLoggedIn()):
                ?>
                <nav class="navbar navbar-expand-lg mb-4 bg-dark-body">
                    <div class="container">
                        <a class="navbar-brand" href="/eduvault/index.php">EduVault
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                            <i class="fas fa-bars color fs-2"></i>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav ms-auto">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo str_contains($_SERVER['PHP_SELF'], 'books') ? 'active' : ''; ?>"
                                        href="/eduvault/books/list.php">Books
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo str_contains($_SERVER['PHP_SELF'], 'files') ? 'active' : ''; ?>"
                                        href="/eduvault/files/list.php">Study Materials
                                    </a>
                                </li>
                                <?php if (isLoggedIn()): ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo str_contains($_SERVER['PHP_SELF'], 'dashboard') ? 'active' : ''; ?>"
                                            href="/eduvault/dashboard/dashboard.php">Dashboard
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/eduvault/logout.php">Logout
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/eduvault/login.php">Login
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/eduvault/register.php">Register
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li class="nav-item dropdown">
                                    <button class="btn nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fas fa-adjust"></i>
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
                <?php
            endif;
        endif;
        if (isLoggedIn()):
            ?>
            <nav class="navbar navbar-expand-lg mb-2 bg-dark-body">
                <div class="container-fluid px-2">
                    <?php if (!empty($sidebar)): ?>
                        <button class="btn fs-2 d-lg-none me-2 fa-color" id="sidebarToggle" type="button">
                            <i class="fas fa-bars"></i>
                        </button>
                    <?php endif; ?>
                    <a class="navbar-brand d-none d-sm-block fw-bold" href="/eduvault/dashboard/dashboard.php">EduVault</a>
                    <a class="navbar-brand d-block d-sm-none fw-bold" href="/eduvault/dashboard/dashboard.php">EV</a>
                    <div class="d-flex align-items-center ms-auto gap-2">
                        <button class="btn d-md-none fa-color" type="button" data-bs-toggle="collapse"
                            data-bs-target="#mobileSearchBar" aria-controls="mobileSearchBar" aria-expanded="false"
                            aria-label="Toggle search">
                            <i class="fas fa-search"></i>
                        </button>
                        <form class="d-none d-md-flex" method="GET" action="/eduvault/files/list.php">
                            <input type="text" name="search" class="form-control input-dark text-white border-0 me-2"
                                style="min-width:300px; max-width:500px;" placeholder="Search...">
                            <button class="btn fa-color" type="submit"><i class="fas fa-search"></i></button>
                        </form>
                        <button class="btn position-relative fa-color" title="Notifications">
                            <i class="fas fa-bell fa-lg"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                style="font-size:0.6em;">0</span>
                        </button>
                        <div class="dropdown">
                            <button class="btn bg-dark-body dropdown-toggle p-0" type="button" id="profileDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?= htmlspecialchars($_SESSION['avatar']) ?>" alt="Avatar" class="rounded-circle"
                                    width="36" height="36">
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                <li><a class="dropdown-item" href="/eduvault/dashboard/dashboard.php">Profile</a></li>
                                <li><button type="button" class="dropdown-item d-flex align-items-center"
                                        data-theme-value="light">Light
                                    </button></li>
                                <li><button type="button" class="dropdown-item d-flex align-items-center"
                                        data-theme-value="dark">Dark
                                    </button></li>
                                <li><button type="button" class="dropdown-item d-flex align-items-center"
                                        data-theme-value="auto">System
                                    </button></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="/eduvault/logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="collapse d-md-none mt-2 px-3 w-100" id="mobileSearchBar">
                    <form class="d-flex" method="GET" action="/eduvault/files/list.php">
                        <input type="text" name="search" class="form-control input-dark border-0 me-2"
                            style="min-width:200px; max-width:100%;" placeholder="Search...">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </nav>
        <?php endif; ?>

        <?php if (!empty($_SESSION['toasts'])): ?>
            <div class="toast-container position-fixed top-0 end-0 p-3 show" style="z-index: 1055;">
                <?php foreach ($_SESSION['toasts'] as $toast): ?>
                    <div class="toast align-items-center text-white bg-<?= toastBgClass($toast['type']) ?> border-0 mb-2"
                        role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <?= htmlspecialchars($toast['message']) ?>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                aria-label="Close"></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['toasts']); ?>
        <?php endif; ?>