<?php
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT id, password FROM users WHERE email = '$email'";
    $result = mysqli_query($mysqli, $query);

    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            $update_ = "UPDATE users SET last_active = current_timestamp() WHERE id = $user[id];";
            mysqli_query($mysqli, $update_);
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard/dashboard.php");
            exit();
        }
    }
    $error = "Invalid email or password";
}
?>
<div class="container-fluid min-vh-100 d-flex justify-content-center align-items-center bg-light px-2">
    <div class="w-100" style="max-width: 400px;">
        <div class="card shadow rounded-4">
            <div class="card-header bg-primary text-white rounded-top-4">
                <h4 class="mb-0 text-center"><i class="fas fa-sign-in-alt me-2"></i>Login</h4>
            </div>
            <div class="card-body p-4">

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3 rounded-pill">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>

                <div class="d-flex justify-content-between gap-2">
                    <a href="index.php" class="btn btn-outline-secondary w-50 rounded-pill">
                        <i class="fas fa-home me-2"></i>Home
                    </a>
                    <a href="register.php" class="btn btn-outline-info w-50 rounded-pill">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>