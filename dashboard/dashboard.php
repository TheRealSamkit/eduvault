<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

requireLogin();

$user_id = $_SESSION['user_id'];

$sidebar = true;
// Handle profile update
if (isset($_POST['save_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $location = trim($_POST['location']);
    $avatar_path = null;
    $update_password = false;

    // Handle avatar upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array($ext, $allowed)) {
            $avatar_name = uniqid() . '.' . $ext;
            $upload_dir = '../uploads/avatars/';
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $avatar_name)) {
                $avatar_path = 'uploads/avatars/' . $avatar_name;
            }
        }
    }

    // Build update query
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
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: dashboard.php?profile_updated=1");
    exit();
}

// User info + avatar
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($mysqli, $user_query);
$user = mysqli_fetch_assoc($user_result);
$_SESSION['avatar'] = !empty($user['avatar_path']) ? "../" . $user['avatar_path'] : '../uploads/avatars/default.png';

// Metrics
$books_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM book_listings WHERE user_id = $user_id"))['count'];
$files_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM digital_files WHERE user_id = $user_id"))['count'];
$downloads_count = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(*) as count FROM downloads WHERE file_id IN (SELECT id FROM digital_files WHERE user_id = $user_id)"))['count'];
$avg_feedback = round(mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT AVG(rating) as avg FROM file_feedback WHERE file_id IN (SELECT id FROM digital_files WHERE user_id = $user_id)"))['avg'] ?? 0, 1);

// Recent uploads
$activity_query = "
    SELECT 'book' as type, id, title, created_at as date FROM book_listings WHERE user_id = $user_id
    UNION
    SELECT 'file' as type, id, title, upload_date as date FROM digital_files WHERE user_id = $user_id
    ORDER BY date DESC LIMIT 5";
$activity_result = mysqli_query($mysqli, $activity_query);

// Recent reports by user
$reports_query = "
    SELECT r.id, r.reason, r.created_at, r.content_type, r.content_id
    FROM reported_content r
    WHERE r.reporter_id = $user_id
    ORDER BY r.created_at DESC LIMIT 5";
$reports_result = mysqli_query($mysqli, $reports_query);

require_once '../includes/header.php';
require_once '../modals/editProfileModal.php';
?>

<style>
    @media (max-width: 991.98px) {
        .main-content {
            margin-left: 0;
        }
    }
</style>
<div class="d-flex align-items-start">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <div class="container-fluid my-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body d-flex align-items-center gap-3">
                    <img src="<?php echo htmlspecialchars($_SESSION['avatar']); ?>"
                        class="rounded-circle img-thumbnail bg-dark" width="90" alt="User Avatar">
                    <div>
                        <h3 class="mb-0"><?php echo htmlspecialchars($user['name']); ?></h3>
                        <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small><br>
                        <small class="text-muted">Joined:
                            <?php echo date("F j, Y", strtotime($user['created_at'])); ?></small><br>
                        <span
                            class="badge bg-secondary mb-1 p-2"><?php echo htmlspecialchars($user['location'] ?: 'Location Unknown'); ?></span>

                        <button class="btn btn-sm btn-outline-info ms-2" data-bs-toggle="modal"
                            data-bs-target="#editProfileModal"><i class="fas fa-user-edit"></i> Edit Profile</button>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            <div class="d-grid gap-2">
                                <a href="../books/add.php" class="btn btn-outline-primary"><i
                                        class="fas fa-book me-2"></i>Add
                                    New Book</a>
                                <a href="../files/upload.php" class="btn btn-outline-success"><i
                                        class="fas fa-upload me-2"></i>Upload File</a>
                                <a href="my_books.php" class="btn btn-outline-secondary"><i
                                        class="fas fa-folder-open me-2"></i>Manage My Books</a>
                                <a href="my_uploads.php" class="btn btn-outline-secondary"><i
                                        class="fas fa-folder-open me-2"></i>Manage My Files</a>
                                <a href="../pages/change_password.php" class="btn btn-outline-primary mb-3"><i
                                        class="fas fa-key me-2"></i>Change Password</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                            <div class="list-group list-group-flush small rounded">
                                <?php if (mysqli_num_rows($activity_result) > 0): ?>
                                    <?php while ($activity = mysqli_fetch_assoc($activity_result)): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i
                                                    class="fas fa-<?php echo $activity['type'] == 'book' ? 'book' : 'file-alt'; ?> me-2"></i>
                                                <?php echo htmlspecialchars($activity['title']); ?>
                                            </div>
                                            <small
                                                class="text-muted"><?php echo date("M j, Y", strtotime($activity['date'])); ?></small>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="list-group-item text-muted">No recent activity.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow-sm text-center p-2">
                        <div class="card-body">
                            <i class="fas fa-book fa-2x mb-2"></i>
                            <h2><?php echo $books_count; ?></h2>
                            <p class="mb-0">Books</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm text-center p-2">
                        <div class="card-body">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <h2><?php echo $files_count; ?></h2>
                            <p class="mb-0">Files</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white shadow-sm text-center p-2">
                        <div class="card-body">
                            <i class="fas fa-download fa-2x mb-2"></i>
                            <h2><?php echo $downloads_count; ?></h2>
                            <p class="mb-0">Downloads</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white shadow-sm text-center p-2">
                        <div class="card-body">
                            <i class="fas fa-star fa-2x mb-2"></i>
                            <h2><?php echo $avg_feedback > 0 ? $avg_feedback : 'N/A'; ?></h2>
                            <p class="mb-0">Avg Feedback</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5><i class="fas fa-flag me-2"></i>Recent Reports by You</h5>
                    <?php if (mysqli_num_rows($reports_result) > 0): ?>
                        <ul class="list-group list-group-flush small rounded">
                            <?php while ($report = mysqli_fetch_assoc($reports_result)): ?>
                                <li class="list-group-item">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($report['content_type']); ?>
                                        #<?php echo $report['content_id']; ?></span>
                                    <?php echo htmlspecialchars($report['reason']); ?>
                                    <small
                                        class="text-muted d-block"><?php echo date("M j, Y, H:i", strtotime($report['created_at'])); ?></small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted">No reports submitted yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>