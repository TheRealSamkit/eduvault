<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

$error = '';
$success = '';

// Update the PHP processing section
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $location = $_POST['location'];
    $phone = $_POST['phone'];
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    // Check if email exists (prepared statement)
    $stmt = mysqli_prepare($mysqli, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error = "Email already exists!";
    } else {
        // Insert user (prepared statement)
        $query = "INSERT INTO users (name, email, password, location, phone, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($mysqli, $query);
        mysqli_stmt_bind_param($insert_stmt, 'sssssss', $name, $email, $password, $location, $phone, $latitude, $longitude);
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
    <div class="w-100" style="max-width: 500px;">
        <div class="card shadow rounded-4">
            <div class="card-header bg-primary text-white rounded-top-4">
                <h4 class="mb-0 text-center"><i class="fas fa-user-plus me-2"></i>Register</h4>
            </div>
            <div class="card-body p-4">

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control bg-dark-body" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control bg-dark-body" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control bg-dark-body" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control bg-dark-body" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control bg-dark-body" required>
                    </div>
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">

                    <button type="submit" class="btn btn-primary w-100 mb-3 rounded-pill">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </button>
                </form>

                <div class="d-flex justify-content-between gap-2">
                    <a href="index.php" class="btn btn-outline-secondary w-50 rounded-pill">
                        <i class="fas fa-home me-2"></i>Home
                    </a>
                    <a href="login.php" class="btn btn-outline-info w-50 rounded-pill">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function (e) {
        e.preventDefault();

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
                e.target.submit();
            }, function () {
                e.target.submit();
            });
        } else {
            e.target.submit();
        }
    });

</script>
<?php require_once 'includes/footer.php'; ?>