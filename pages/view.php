<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location:" . $_SERVER['HTTP_REFERER']);
    exit();
}

$get_user_id = intval(mysqli_real_escape_string($mysqli, $_GET["id"]));
$query = "SELECT * FROM users WHERE id = $get_user_id";
$result = mysqli_query($mysqli, $query);

// Get user's books count
$books_query = "SELECT COUNT(*) as book_count FROM book_listings WHERE user_id = $get_user_id";
$books_result = mysqli_query($mysqli, $books_query);
$books_count = mysqli_fetch_assoc($books_result)['book_count'];

// Get user's files count
$files_query = "SELECT COUNT(*) as file_count FROM digital_files WHERE user_id = $get_user_id";
$files_result = mysqli_query($mysqli, $files_query);
$files_count = mysqli_fetch_assoc($files_result)['file_count'];

// Get total downloads of user's files
$downloads_query = "SELECT COUNT(*) as total_downloads FROM downloads WHERE user_id = $get_user_id";
$downloads_result = mysqli_query($mysqli, $downloads_query);
$total_downloads = mysqli_fetch_assoc($downloads_result)['total_downloads'] ?? 0;

// Get average feedback rating for user's uploaded files
$feedback_query = "SELECT AVG(rating) as avg_feedback FROM file_feedback WHERE file_id IN (SELECT id FROM digital_files WHERE user_id = $get_user_id)";
$feedback_result = mysqli_query($mysqli, $feedback_query);
$avg_feedback = round(mysqli_fetch_assoc($feedback_result)['avg_feedback'] ?? 0, 1);

// Get recent books
$recent_books_query = "SELECT id, title FROM book_listings WHERE user_id = $get_user_id ORDER BY created_at DESC LIMIT 5";
$recent_books = mysqli_query($mysqli, $recent_books_query);

// Get recent files
$recent_files_query = "SELECT id, title FROM digital_files WHERE user_id = $get_user_id ORDER BY upload_date DESC LIMIT 5";
$recent_files = mysqli_query($mysqli, $recent_files_query);
if (isset($_POST['submit_report']) && isLoggedIn()) {
    $report_reason = mysqli_real_escape_string($mysqli, trim($_POST['report_reason']));
    $reporter_id = $_SESSION['user_id'];

    if (!empty($report_reason)) {
        $insert_report = "INSERT INTO reported_content (reporter_id, content_type, content_id, reason) 
                          VALUES ($reporter_id, 'user', $get_user_id, '$report_reason')";
        if (mysqli_query($mysqli, $insert_report)) {
            $_SESSION['success'] = "Thank you for your report. We'll review it soon.";
        } else {
            $_SESSION['error'] = "Failed to submit report. Please try again later.";
        }
    } else {
        $_SESSION['error'] = "Please provide a reason for your report.";
    }
    header("Location: view.php?id=$get_user_id#report");
    exit();
}

?>
<div class="container-md p-0 card mb-3">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php $user = mysqli_fetch_assoc($result); ?>
        <?php $avatar = !empty($user['avatar_path']) ? "../uploads/avatars/" . $user['avatar_path'] : '../uploads/avatars/default.png'; ?>

        <div class="card-header d-flex align-items-center gap-3">
            <img src="<?php echo htmlspecialchars($avatar); ?>" class="rounded-circle img-thumbnail bg-dark" width="80"
                alt="User Avatar">
            <div>
                <h2 class="mb-0"><?php echo htmlspecialchars($user['name']); ?>'s Profile</h2>
                <?php if (isLoggedIn() && $get_user_id != $_SESSION['user_id']): ?>
                    <button class="btn btn-danger btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        <i class="fas fa-flag me-1"></i> Report User
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
                                <h6 class="card-title"><i class="fas fa-file-alt me-2"></i>My Files</h6>
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
                <div class="card-header bg-light"><strong>Recent Contributions</strong></div>
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

require_once '../modals/reportmodal.php';
require_once '../includes/footer.php'; ?>