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

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Login</h4>
            </div>
            <div class="card-body">
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
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>