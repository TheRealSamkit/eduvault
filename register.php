<?php
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

$error = '';
$success = '';

// Update the PHP processing section
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($mysqli, $_POST['name']);
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $location = mysqli_real_escape_string($mysqli, $_POST['location']);
    $phone = mysqli_real_escape_string($mysqli, $_POST['phone']);
    $latitude = !empty($_POST['latitude']) ? mysqli_real_escape_string($mysqli, $_POST['latitude']) : NULL;
    $longitude = !empty($_POST['longitude']) ? mysqli_real_escape_string($mysqli, $_POST['longitude']) : NULL;

    $check_email = mysqli_query($mysqli, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $error = "Email already exists!";
    } else {
        $query = "INSERT INTO users (name, email, password, location, phone, latitude, longitude) 
                  VALUES ('$name', '$email', '$password', '$location', '$phone', $latitude, $longitude)";

        if (mysqli_query($mysqli, $query)) {
            $success = "Registration successful! Please login.";
        } else {
            $error = "Registration failed: " . mysqli_error($mysqli);
        }
    }
}

?>
<script>
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
            }, function (error) {
                console.log("Error getting location: " + error.message);
            });
        }
    }
</script>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Register</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="" onsubmit="getLocation()">
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
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>