<?php
require_once '../includes/db_connect.php';
include '../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($mysqli, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error = "Email already exists!";
    } else {
        $query = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $insert_stmt = mysqli_prepare($mysqli, $query);
        mysqli_stmt_bind_param($insert_stmt, 'sss', $name, $email, $password);
        if (mysqli_stmt_execute($insert_stmt)) {
            $success = "Registration successful! Please login.";
        } else {
            $error = "Registration failed: " . mysqli_error($mysqli);
        }
        mysqli_stmt_close($insert_stmt);
    }
    mysqli_stmt_close($stmt);
}

?>
<div class="container-fluid min-vh-100 d-flex justify-content-center align-items-center px-2">
    <div class="w-100" style="max-width: 450px;">
        <div class="card shadow rounded-4">
            <div class="card-header bg-primary text-white rounded-top-4">
                <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Create an Account </h4>
            </div>
            <div class="card-body p-4">

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <script src="https://accounts.google.com/gsi/client" async defer></script>
                    <div id="g_id_onload"
                        data-client_id="982609216899-e94n99lb6b4mi9n1gdbs395at8lrt6hc.apps.googleusercontent.com"
                        data-context="signup" data-ux_mode="popup"
                        data-login_uri="http://localhost/eduvault/auth/google-callback.php" data-auto_prompt="false">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
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

                    <p>Already have an account?<a href="/eduvault/auth/login.php" class=""> Sign in</a></p>
                    <button type="submit" class="btn btn-primary mb-2 w-100">Create Account
                    </button>
                </form>
                <div class="text-center">
                    <p class="text-muted mb-2">or</p>
                    <button id="google-login-btn" class="btn w-100">
                        Continue with Google
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>