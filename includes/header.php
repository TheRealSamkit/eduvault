<?php require_once 'session.php'; ?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EduVault - Educational Resource Sharing</title>
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
    </head>

    <body>
        <nav class="navbar navbar-expand-lg navbar-light mb-4">
            <div class="container">
                <a class="navbar-brand" href="/eduvaultv2/index.php">
                    <i class="fas fa-book-reader me-2"></i>EduVault
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/eduvaultv2/books/list.php">
                                <i class="fas fa-book me-1"></i>Books
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/eduvaultv2/files/list.php">
                                <i class="fas fa-file-alt me-1"></i>Study Materials
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/eduvaultv2/dashboard/dashboard.php">
                                    <i class="fas fa-user me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/eduvaultv2/logout.php">
                                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/eduvaultv2/login.php">
                                    <i class="fas fa-sign-in-alt me-1"></i>Login
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/eduvaultv2/register.php">
                                    <i class="fas fa-user-plus me-1"></i>Register
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
        </div>