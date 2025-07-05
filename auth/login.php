<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
$error = '';
session_start();
if (isset($_GET['redirect'])) {
    $_SESSION['referred'] = mysqli_real_escape_string($mysqli, $_GET['redirect']);
} elseif (isset($_SESSION['user_id'])) {
    redirect('dashboard/dashboard.php');
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($mysqli, "SELECT id, password FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            $update_stmt = mysqli_prepare($mysqli, "UPDATE users SET last_active = current_timestamp() WHERE id = ?");
            mysqli_stmt_bind_param($update_stmt, 'i', $user['id']);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
            $_SESSION['user_id'] = $user['id'];
            if (isset($_SESSION['referred'])) {
                unset($_SESSION['referred']);

                $redirect = (str_contains($_SESSION['referred'], 'index.php') || str_contains($_SESSION['referred'], 'login.php'))
                    ? '../dashboard/dashboard.php'
                    : $_SESSION['referred'];

                unset($_SESSION['referred']);
                redirect($redirect);
            } else {
                redirect('../dashboard/dashboard.php');
            }
        }
    }
    $error = "Invalid email or password";
}
session_write_close();
include '../includes/header.php';
?>
<div class="container-fluid min-vh-100 d-flex justify-content-center align-items-center px-2">
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
                        <input type="email" name="email" class="form-control bg-dark-body" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" id="passwordInput" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <p>Don't have an account?<a href="/eduvault/auth/register.php"> Sign up</a></p>
                    <button type="submit" class="btn btn-primary w-100 mb-1">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
                <div class="text-center">
                    <p class="text-muted mb-2">or</p>
                    <div id="g_id_onload"
                        data-client_id="982609216899-e94n99lb6b4mi9n1gdbs395at8lrt6hc.apps.googleusercontent.com"
                        data-context="signin" data-ux_mode="redirect"
                        data-login_uri="http://localhost/eduvault/auth/google-callback.php" data-auto_prompt="false">
                    </div>
                    <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline"
                        data-text="sign_in_with" data-shape="rectangular" data-logo_alignment="left">
                    </div>
                </div>

                <script src="https://accounts.google.com/gsi/client" async defer></script>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>