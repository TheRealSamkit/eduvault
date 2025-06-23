<?php

$user_id = $_SESSION['user_id'] ?? null;

if (isset($_POST['save_profile']) && $user_id === isset($_GET['id'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $location = trim($_POST['location']);
    $avatar_path = null;

    $result = mysqli_query($mysqli, "SELECT avatar_path FROM users WHERE id = $user_id");
    $row = mysqli_fetch_assoc($result);
    $old_avatar = $row['avatar_path'] ?? 'uploads/avatars/default.png';

    // Handle avatar upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array($ext, $allowed)) {
            $avatar_name = uniqid() . '.' . $ext;
            $upload_dir = '../uploads/avatars/';
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $avatar_name)) {
                $avatar_path = 'uploads/avatars/' . $avatar_name;
                if ($old_avatar !== 'uploads/avatars/default.png' && file_exists('../' . $old_avatar)) {
                    unlink('../' . $old_avatar);
                }
            } else {
                flash('error', 'Failed to upload avatar. Please try again.');
                redirect("dashboard.php?profile_updated=0");
                exit;
            }
        } else {
            flash('error', 'Invalid file type for avatar. Only JPG, JPEG, PNG allowed.');
            redirect("dashboard.php?profile_updated=0");
            exit;
        }
    }

    $query = "UPDATE users SET name=?, email=?, phone=?, location=?";
    $params = [$name, $email, $phone, $location];
    $types = "ssss";

    if ($avatar_path) {
        $query .= ", avatar_path=?";
        $params[] = $avatar_path;
        $types .= "s";
    }

    $query .= " WHERE id=?";
    $params[] = $user_id;
    $types .= "i";

    $stmt = mysqli_prepare($mysqli, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        if (mysqli_stmt_execute($stmt)) {
            flash('success', 'Profile updated successfully.');
        } else {
            flash('error', 'Failed to update profile. Please try again.');
        }
        mysqli_stmt_close($stmt);
    } else {
        flash('error', 'Database error. Please try again later.');
    }

    redirect("dashboard.php?profile_updated=1");
    exit();
}

?>
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 text-center">
                    <img src="<?php echo htmlspecialchars($_SESSION['avatar'] ?? '../uploads/avatars/default.png'); ?>"
                        class="rounded-circle img-thumbnail bg-dark" width="80" alt="User Avatar">
                </div>
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control bg-dark-body"
                        value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control bg-dark-body"
                        value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control bg-dark-body"
                        value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control bg-dark-body"
                        value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Avatar</label>
                    <input type="file" name="avatar" class="form-control bg-dark-body" accept=".jpg,.jpeg,.png">
                    <div class="form-text">Leave empty to keep current avatar</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="save_profile" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>