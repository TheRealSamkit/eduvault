<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/header.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current password hash
    $stmt = mysqli_prepare($mysqli, "SELECT password FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $db_password);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!password_verify($current_password, $db_password)) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = mysqli_prepare($mysqli, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($update_stmt, 'si', $new_hash, $user_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
        $success = 'Password updated successfully!';
    }
}
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control bg-dark-body" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control bg-dark-body" required
                                minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control bg-dark-body" required
                                minlength="6">
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Update
                            Password</button>
                        <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary w-100 mt-2"><i
                                class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>