<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect("../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    redirect("" . $_SERVER['HTTP_REFERER']);
    exit();
}
$get_user_id = intval($_GET["id"]);
$viewer_type = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $get_user_id;

// User info
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $get_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// $books_count = getCount($mysqli, 'book_listings', 'book_count', $get_user_id);
$files_count = getCount($mysqli, 'digital_files', 'file_count', $get_user_id);
$total_downloads = getCount($mysqli, 'downloads', 'total_downloads', $get_user_id) ?? 0;

// Get average feedback rating for user's uploaded files
$feedback_query = "SELECT AVG(rating) as avg_feedback FROM file_feedback WHERE file_id IN (SELECT id FROM digital_files WHERE user_id = ?)";
$feedback_stmt = mysqli_prepare($mysqli, $feedback_query);
mysqli_stmt_bind_param($feedback_stmt, 'i', $get_user_id);
mysqli_stmt_execute($feedback_stmt);
$feedback_result = mysqli_stmt_get_result($feedback_stmt);
$avg_feedback = round(mysqli_fetch_assoc($feedback_result)['avg_feedback'] ?? 0, 1);
mysqli_stmt_close($feedback_stmt);

// // Get recent books
// $recent_books_query = "SELECT id, title FROM book_listings WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
// $recent_books_stmt = mysqli_prepare($mysqli, $recent_books_query);
// mysqli_stmt_bind_param($recent_books_stmt, 'i', $get_user_id);
// mysqli_stmt_execute($recent_books_stmt);
// $recent_books = mysqli_stmt_get_result($recent_books_stmt);

// Get recent files
$recent_files_query = "SELECT id, title FROM digital_files WHERE user_id = ? ORDER BY upload_date DESC LIMIT 5";
$recent_files_stmt = mysqli_prepare($mysqli, $recent_files_query);
mysqli_stmt_bind_param($recent_files_stmt, 'i', $get_user_id);
mysqli_stmt_execute($recent_files_stmt);
$recent_files = mysqli_stmt_get_result($recent_files_stmt);
include '../includes/header.php';
require_once '../modals/reportmodal.php';
require_once '../modals/avatarModal.php';
$test = intval($_SESSION['user_id']) == isset($_GET['id']) ? intval($_GET['id']) : 0; // Set to true to include the edit profile modal
if ($test) {
    require_once '../modals/editProfileModal.php';
}
?>
<div class="container-md p-0 card mb-3">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php $user = mysqli_fetch_assoc($result); ?>
        <?php $avatar = !empty($user['avatar_path']) ? "../" . $user['avatar_path'] : '../uploads/avatars/default.png'; ?>

        <div class="card-header d-flex align-items-center gap-3">
            <img src="<?php echo htmlspecialchars($avatar); ?>" class="rounded-circle img-thumbnail bg-dark" width="80"
                alt="User Avatar" data-bs-toggle="modal" data-bs-target="#avatarModal">
            <div>
                <h2 class="mb-0 h3"><?php echo htmlspecialchars($user['name']); ?>'s Profile</h2>
                <p class="text-start text-muted m-0 my-1">
                    <?php if ($viewer_type): ?>
                        <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($user['email']); ?><br>
                    <?php endif; ?>
                    <i class="fas fa-calendar me-1"></i> Member since
                    <?php echo date("F Y", strtotime($user['created_at'])); ?>
                </p>
                <?php if (!$viewer_type): ?>
                    <button class="btn btn-danger btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
                        data-content-type="user" data-report-id="<?php echo $user['id']; ?>"
                        data-report-title="<?php echo htmlspecialchars($user['name']); ?>">
                        <i class="fas fa-flag me-1"></i> Report User
                    </button>
                <?php elseif ($viewer_type): ?>
                    <button class="btn btn-info btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="fas fa-user-edit me-1"></i> Edit Profile
                    </button>
                    <a href="/eduvault/pages/change_password.php" class="btn btn-outline-secondary btn-sm mt-1"><i
                            class="fas fa-key me-1"></i> Change
                        Password</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3 g-2">
                <div class="col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-bookmark me-2"></i>Total Bookmarks</h6>
                            <h2 class="mb-0"><?php echo 1; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-file-alt me-2"></i>Total Files</h6>
                            <h2 class="mb-0"><?php echo $files_count; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-download me-2"></i>Total Downloads</h6>
                            <h2 class="mb-0"><?php echo $total_downloads; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-lg-auto">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-star me-2"></i>Avg. Feedback</h6>
                            <h2 class="mb-0">
                                <?php echo $avg_feedback > 0 ? $avg_feedback . '/5' : 'No feedback yet'; ?>
                            </h2>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card mt-4">
                <div class="card-header"><strong>Recent Contributions</strong></div>
                <div class="card-body">
                    <ul>
                        <?php if (mysqli_num_rows($recent_files) > 0): ?>
                            <?php while ($file = mysqli_fetch_assoc($recent_files)): ?>
                                <li><a
                                        href="../files/view.php?id=<?php echo $file['id']; ?>"><?php echo htmlspecialchars($file['title']); ?></a>
                                </li>
                            <?php endwhile; ?>
                        <?php else:
                            if ($viewer_type): ?>

                                <li>You haven't uploaded any files yet. Start sharing your knowledge —
                                    <a href="/eduvault/files/upload.php">upload your first document
                                        now!</a>
                                </li>
                            <?php else: ?>
                                <li>This user hasn't uploaded anything yet.</li>
                            <?php endif;
                        endif; ?>
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