<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../modals/editProfileModal.php';

require_once '../modals/reportmodal.php';
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location:" . $_SERVER['HTTP_REFERER']);
    exit();
}

$get_user_id = intval($_GET["id"]);

// Handle profile update (only if viewing own profile)
if (isset($_POST['save_profile']) && $_SESSION['user_id'] === $get_user_id) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $location = trim($_POST['location']);
    $avatar_path = null;
    $password = $_POST['password'];
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

    // If password is set, hash it
    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $update_password = true;
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
    if ($update_password) {
        $query .= ", password=?";
        $params[] = $password;
        $types .= "s";
    }
    $query .= " WHERE id=?";
    $params[] = $get_user_id;
    $types .= "i";

    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: view.php?id=$get_user_id&profile_updated=1");
    exit();
}

// User info
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $get_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get user's books count
$books_query = "SELECT COUNT(*) as book_count FROM book_listings WHERE user_id = ?";
$books_stmt = mysqli_prepare($mysqli, $books_query);
mysqli_stmt_bind_param($books_stmt, 'i', $get_user_id);
mysqli_stmt_execute($books_stmt);
$books_result = mysqli_stmt_get_result($books_stmt);
$books_count = mysqli_fetch_assoc($books_result)['book_count'];
mysqli_stmt_close($books_stmt);

// Get user's files count
$files_query = "SELECT COUNT(*) as file_count FROM digital_files WHERE user_id = ?";
$files_stmt = mysqli_prepare($mysqli, $files_query);
mysqli_stmt_bind_param($files_stmt, 'i', $get_user_id);
mysqli_stmt_execute($files_stmt);
$files_result = mysqli_stmt_get_result($files_stmt);
$files_count = mysqli_fetch_assoc($files_result)['file_count'];
mysqli_stmt_close($files_stmt);

// Get total downloads of user's files
$downloads_query = "SELECT COUNT(*) as total_downloads FROM downloads WHERE user_id = ?";
$downloads_stmt = mysqli_prepare($mysqli, $downloads_query);
mysqli_stmt_bind_param($downloads_stmt, 'i', $get_user_id);
mysqli_stmt_execute($downloads_stmt);
$downloads_result = mysqli_stmt_get_result($downloads_stmt);
$total_downloads = mysqli_fetch_assoc($downloads_result)['total_downloads'] ?? 0;
mysqli_stmt_close($downloads_stmt);

// Get average feedback rating for user's uploaded files
$feedback_query = "SELECT AVG(rating) as avg_feedback FROM file_feedback WHERE file_id IN (SELECT id FROM digital_files WHERE user_id = ?)";
$feedback_stmt = mysqli_prepare($mysqli, $feedback_query);
mysqli_stmt_bind_param($feedback_stmt, 'i', $get_user_id);
mysqli_stmt_execute($feedback_stmt);
$feedback_result = mysqli_stmt_get_result($feedback_stmt);
$avg_feedback = round(mysqli_fetch_assoc($feedback_result)['avg_feedback'] ?? 0, 1);
mysqli_stmt_close($feedback_stmt);

// Get recent books
$recent_books_query = "SELECT id, title FROM book_listings WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$recent_books_stmt = mysqli_prepare($mysqli, $recent_books_query);
mysqli_stmt_bind_param($recent_books_stmt, 'i', $get_user_id);
mysqli_stmt_execute($recent_books_stmt);
$recent_books = mysqli_stmt_get_result($recent_books_stmt);

// Get recent files
$recent_files_query = "SELECT id, title FROM digital_files WHERE user_id = ? ORDER BY upload_date DESC LIMIT 5";
$recent_files_stmt = mysqli_prepare($mysqli, $recent_files_query);
mysqli_stmt_bind_param($recent_files_stmt, 'i', $get_user_id);
mysqli_stmt_execute($recent_files_stmt);
$recent_files = mysqli_stmt_get_result($recent_files_stmt);

if (isset($_POST['submit_report']) && isLoggedIn()) {
    $report_reason = trim($_POST['report_reason']);
    $reporter_id = $_SESSION['user_id'];

    if (!empty($report_reason)) {
        $insert_report = "INSERT INTO reported_content (reporter_id, content_type, content_id, reason) VALUES (?, 'user', ?, ?)";
        $report_stmt = mysqli_prepare($mysqli, $insert_report);
        mysqli_stmt_bind_param($report_stmt, 'iis', $reporter_id, $get_user_id, $report_reason);
        if (mysqli_stmt_execute($report_stmt)) {
            flash('success', "Report submitted successfully. Thank you for helping us keep the platform safe.");
        } else {
            flash('error', "Failed to submit report. Please try again later.");
        }
        mysqli_stmt_close($report_stmt);
    } else {
        flash('error', "Report reason cannot be empty.");
    }
    header("Location: view.php?id=$get_user_id#report");
    exit();
}

require_once '../includes/header.php';
?>
<div class="container-md p-0 card mb-3">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php $user = mysqli_fetch_assoc($result); ?>
        <?php $avatar = !empty($user['avatar_path']) ? "../" . $user['avatar_path'] : '../uploads/avatars/default.png'; ?>

        <div class="card-header d-flex align-items-center gap-3">
            <img src="<?php echo htmlspecialchars($avatar); ?>" class="rounded-circle img-thumbnail bg-dark" width="80"
                alt="User Avatar">
            <div>
                <h2 class="mb-0"><?php echo htmlspecialchars($user['name']); ?>'s Profile</h2>
                <?php if (isLoggedIn() && $get_user_id != $_SESSION['user_id']): ?>
                    <button class="btn btn-danger btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
                        data-content-type="user" data-report-id="<?php echo $user['id']; ?>"
                        data-report-title="<?php echo htmlspecialchars($user['name']); ?>">
                        <i class="fas fa-flag me-1"></i> Report User
                    </button>
                <?php elseif (isLoggedIn() && $get_user_id == $_SESSION['user_id']): ?>
                    <button class="btn btn-info btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="fas fa-user-edit me-1"></i> Edit Profile
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($user['location']); ?></p>
                    <p><strong>Joined:</strong> <?php echo date("F j, Y", strtotime($user['created_at'])); ?></p>
                </div>

                <div class="col-md-8 row g-2">
                    <div class="col-md-6">
                        <div class="card bg-primary text-white shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-book me-2"></i>Total Books</h6>
                                <h2 class="mb-0"><?php echo $books_count; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-success text-white shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-file-alt me-2"></i>Total Files</h6>
                                <h2 class="mb-0"><?php echo $files_count; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-info text-white shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-download me-2"></i>Total Downloads</h6>
                                <h2 class="mb-0"><?php echo $total_downloads; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-warning text-dark shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-star me-2"></i>Avg. Feedback</h6>
                                <h2 class="mb-0"><?php echo $avg_feedback > 0 ? $avg_feedback : 'N/A'; ?>/5</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header"><strong>Recent Contributions</strong></div>
                <div class="card-body">
                    <h6>Books:</h6>
                    <ul>
                        <?php if (mysqli_num_rows($recent_books) > 0): ?>
                            <?php while ($book = mysqli_fetch_assoc($recent_books)): ?>
                                <li><a
                                        href="../books/view.php?id=<?php echo $book['id']; ?>"><?php echo htmlspecialchars($book['title']); ?></a>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li>No recent books.</li>
                        <?php endif; ?>
                    </ul>
                    <h6>Files:</h6>
                    <ul>
                        <?php if (mysqli_num_rows($recent_files) > 0): ?>
                            <?php while ($file = mysqli_fetch_assoc($recent_files)): ?>
                                <li><a
                                        href="../files/view.php?id=<?php echo $file['id']; ?>"><?php echo htmlspecialchars($file['title']); ?></a>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li>No recent files.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card-body">
            <div class="alert alert-warning">
                No user found with this ID.
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once '../includes/footer.php'; ?>