<?php
require_once 'session.php';
$additionalScripts[] = 'loader.js';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault - Educational Resource Sharing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e88e5;
            --primary-dark: #1565c0;
            --secondary-color: #757575;
            --success-color: #43a047;
            --warning-color: #fdd835;
            --danger-color: #e53935;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-primary {
            background-color: var(--primary-color) !important;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: bold;
        }

        .nav-link {
            color: var(--secondary-color) !important;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link.active {
            color: var(--primary-color) !important;
            font-weight: bold;
        }
    </style>
    <?php
    if (isset($additionalStyles)) {
        foreach ($additionalStyles as $style) {
            echo "<link rel='stylesheet' href='http://localhost/eduvault/assets/css/$style'>";
        }
    }
    ?>
</head>

<body>
    <!-- Page Loader -->
    <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100 bg-white"
        style="z-index: 9999;" id="pageLoader">
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h4 class="text-primary">Loading EduVault...</h4>
        </div>
    </div>
    <?php if (!str_contains($_SERVER['PHP_SELF'], 'login') && !str_contains($_SERVER['PHP_SELF'], 'register')): ?>

        <nav class="navbar navbar-expand-lg navbar-light mb-4">
            <div class="container">
                <a class="navbar-brand" href="/eduvault/index.php">
                    <i class="fas fa-book-reader me-2"></i>EduVault
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/eduvault/books/list.php">
                                <i class="fas fa-book me-1"></i>Books
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/eduvault/files/list.php">
                                <i class="fas fa-file-alt me-1"></i>Study Materials
                            </a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/eduvault/dashboard/dashboard.php">
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