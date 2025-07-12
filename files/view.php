<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!isset($_GET['slug'])) {
    redirect("list.php");
    exit();
}

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$query = "SELECT f.*, s.name as subject, c.name as course, y.year as year, u.name as uploader_name, u.id as uploader_id, (SELECT COUNT(*) FROM downloads WHERE file_id = f.id) as download_count, (SELECT COUNT(*) FROM reported_content WHERE content_id = f.id) as report_count, (SELECT AVG(rating) FROM file_feedback WHERE file_id = f.id) as avg_rating FROM digital_files f JOIN users u ON f.user_id = u.id LEFT JOIN subjects s ON f.subject_id = s.id LEFT JOIN courses c ON f.course_id = c.id LEFT JOIN years y ON f.year_id = y.id WHERE f.slug = ?";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$file = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$feedback_query = "SELECT f.*, u.name as user_name
                  FROM file_feedback f 
                  JOIN users u ON f.user_id = u.id 
                  WHERE f.file_id = ? 
                  ORDER BY f.created_at DESC";
$feedback_stmt = mysqli_prepare($mysqli, $feedback_query);
mysqli_stmt_bind_param($feedback_stmt, 'i', $file['id']);
mysqli_stmt_execute($feedback_stmt);
$feedback_result = mysqli_stmt_get_result($feedback_stmt);

if (!$file) {
    flash("error", 'File not found or does not exist.');
    redirect("list.php");
    exit();
}

// Token check for file access
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    if (!checkAndConsumeToken($user_id, $file['id'], $mysqli)) {
        flash('error', 'You do not have enough tokens to access this file. Upload files to earn more tokens!');
        redirect('../dashboard/dashboard.php');
        exit();
    }
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn() && isset($_POST['submit_feedback'])) {
    $rating = (int) $_POST['rating'];
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        flash('error', 'Invalid rating value. Please select a rating between 1 and 5.');
        redirect("view.php?slug=$slug");
        exit();
    }

    // Check if user has already given feedback
    $check_query = "SELECT id FROM file_feedback WHERE file_id = ? AND user_id = ?";
    $check_stmt = mysqli_prepare($mysqli, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'ii', $file['id'], $user_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        flash('info', 'You have already submitted feedback for this file.');
        redirect("view.php?slug=$slug");
        exit();
    }

    // Insert new feedback
    $insert_query = "INSERT INTO file_feedback (file_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
    $insert_stmt = mysqli_prepare($mysqli, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, 'iiis', $file['id'], $user_id, $rating, $comment);

    if (mysqli_stmt_execute($insert_stmt)) {
        flash('success', 'Thank you for your feedback!');
    } else {
        flash('error', 'Failed to submit feedback. Please try again later.');
    }

    mysqli_stmt_close($insert_stmt);
    mysqli_stmt_close($check_stmt);

    // Redirect to refresh the page
    redirect("view.php?slug=$slug");
    exit();
}

if (isset($_POST['submit_report']) && isLoggedIn()) {
    $report_reason = trim($_POST['report_reason']);
    $reporter_id = $_SESSION['user_id'];

    if (!empty($report_reason)) {
        $insert_report = "INSERT INTO reported_content (reporter_id, content_type, content_id, reason) VALUES (?, 'file', ?, ?)";
        $report_stmt = mysqli_prepare($mysqli, $insert_report);
        mysqli_stmt_bind_param($report_stmt, 'iis', $reporter_id, $file['id'], $report_reason);
        if (mysqli_stmt_execute($report_stmt)) {
            flash('success', 'Report submitted successfully. Thank you for helping us keep the platform safe.');
        } else {
            flash('error', 'Failed to submit report. Please try again later.');
        }
        mysqli_stmt_close($report_stmt);
    } else {
        flash('error', 'Report reason cannot be empty.');
    }
    redirect("view.php?slug=$slug#report");
    exit();
}

// After fetching $file and before rendering the card
$owner_type = '';
if (isLoggedIn()) {
    if ($_SESSION['user_id'] == $file['uploader_id']) {
        $owner_type = 'You';
    } elseif (isset($_SESSION['admin_id'])) {
        $owner_type = 'Admin';
    } else {
        $owner_type = 'User';
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12 col-md-8 mb-4 mb-md-0">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="mb-0 d-flex align-items-center gap-2">
                            <i
                                class="fas fa-file-<?php echo getFileIcon(strtolower($file['file_type'])); ?> me-2 text-primary"></i>
                            <?php echo htmlspecialchars($file['title']); ?>
                        </h3>

                        <?php if (isLoggedIn()): ?>
                            <?php $bookmarked = isFileBookmarked($_SESSION['user_id'], $file['id'], $mysqli); ?>
                            <div class="ms-auto">
                                <button class="btn btn-md btn-outline-warning btn-bookmark-file"
                                    data-file-id="<?php echo $file['id']; ?>"
                                    title="<?php echo $bookmarked ? 'Remove Bookmark' : 'Add Bookmark'; ?>">
                                    <i class="<?php echo $bookmarked ? 'fas' : 'far'; ?> fa-bookmark"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="mb-2 text-body"><?php echo nl2br(htmlspecialchars($file['description'])); ?></p>
                    <div class="mb-2">
                        <span class="badge bg-primary me-1"><?php echo htmlspecialchars($file['subject']); ?></span>
                        <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($file['course']); ?></span>
                        <span class="badge bg-success me-1"><?php echo $file['year']; ?></span>
                        <?php $all_tags = array_filter(array_map('trim', explode(',', $file['tags']))); ?>
                        <?php if (!empty($all_tags)): ?>
                            <span class="badge bg-body-secondary text-body me-1" data-bs-toggle="tooltip"
                                title="<?php echo htmlspecialchars(implode(', ', $all_tags)); ?>">
                                <i
                                    class="fas fa-tags me-1"></i><?php echo htmlspecialchars($all_tags[0]); ?><?php if (count($all_tags) > 1): ?>
                                    +<?php echo count($all_tags) - 1; ?><?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-2 d-flex flex-wrap gap-2">
                        <span
                            class="badge bg-<?php echo $file['status'] === 'active' ? 'success' : 'secondary'; ?> text-body">Status:
                            <?php echo ucfirst($file['status'] ?? 'N/A'); ?></span>
                        <span
                            class="badge bg-<?php echo $file['visibility'] === 'public' ? 'info' : 'secondary'; ?> text-body">Visibility:
                            <?php echo ucfirst($file['visibility'] ?? 'N/A'); ?></span>
                        <span
                            class="badge bg-<?php echo $file['verified'] ? 'success' : 'danger'; ?> text-body">Verified:
                            <?php echo $file['verified'] ? 'Yes' : 'No'; ?></span>
                    </div>
                    <div class="mb-2 d-flex flex-wrap gap-3">
                        <span class="text-muted"><i
                                class="fas fa-file me-1"></i><?php echo strtoupper($file['file_type']); ?></span>
                        <span class="text-muted"><i class="fas fa-hashtag me-1"></i><span data-bs-toggle="tooltip"
                                title="<?php echo htmlspecialchars($file['content_hash']); ?>"><?php echo htmlspecialchars(substr($file['content_hash'], 0, 8)); ?>...</span></span>
                        <span class="text-muted"><i class="fas fa-key me-1"></i><span data-bs-toggle="tooltip"
                                title="<?php echo htmlspecialchars($file['keywords']); ?>"><?php echo htmlspecialchars(substr($file['keywords'], 0, 16)); ?>...</span></span>
                        <span class="text-muted"><i
                                class="fas fa-database me-1"></i><?php echo formatFileSize($file['file_size']); ?></span>
                    </div>
                    <div class="mb-2 text-body">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($file['uploader_name']); ?>
                        <?php if (!empty($owner_type)): ?>
                            <span class="badge bg-body-secondary text-body ms-1"><?php echo $owner_type; ?></span>
                        <?php endif; ?>
                        <span class="ms-2"><i
                                class="fas fa-calendar-alt me-1"></i><?php echo date('M d, Y', strtotime($file['upload_date'])); ?></span>
                    </div>
                    <div class="mb-2 d-flex flex-wrap gap-2">
                        <span class="text-info"><i
                                class="fas fa-download me-1"></i><?php echo $file['download_count']; ?></span>
                        <span class="text-warning"><i
                                class="fas fa-star me-1"></i><?php echo number_format($file['avg_rating'], 1); ?></span>
                        <span class="text-danger"><i
                                class="fas fa-flag me-1"></i><?php echo $file['report_count']; ?></span>
                    </div>
                    <?php if (isLoggedIn()): ?>
                        <a href="download.php?slug=<?php echo urlencode($file['slug']); ?>" class="btn btn-success mt-2">
                            <i class="fas fa-download me-2"></i>Download File
                        </a>
                        <?php if (strtolower($file['file_type']) === 'pdf'): ?>
                            <a href="/eduvault/pdfjs/web/viewer.php?slug=<?php echo urlencode($file['slug']); ?>"
                                target="_blank" class="btn btn-outline-secondary mt-2 ms-2" title="Full Page PDF Preview">
                                <i class="fas fa-eye me-1"></i>Preview
                            </a>
                        <?php elseif (in_array(strtolower($file['file_type']), ['txt', 'csv', 'md'])): ?>
                            <a href="txt_preview.php?slug=<?php echo urlencode($file['slug']); ?>" target="_blank"
                                class="btn btn-outline-secondary mt-2 ms-2" title="Full Page Text Preview">
                                <i class="fas fa-eye me-1"></i>Preview
                            </a>
                        <?php elseif (in_array(strtolower($file['file_type']), ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                            <button type="button" class="btn btn-outline-secondary mt-2 ms-2 btn-preview-file"
                                data-file-slug="<?php echo urlencode($file['slug']); ?>"
                                data-file-type="<?php echo strtolower($file['file_type']); ?>"
                                data-file-title="<?php echo htmlspecialchars($file['title']); ?>" data-preview-type="image">
                                <i class="fas fa-eye me-1"></i>Preview
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline-secondary mt-2 ms-2 btn-preview-file"
                                data-file-slug="<?php echo urlencode($file['slug']); ?>"
                                data-file-type="<?php echo strtolower($file['file_type']); ?>"
                                data-file-title="<?php echo htmlspecialchars($file['title']); ?>">
                                <i class="fas fa-eye me-1"></i>Preview
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header ">
                    <h4 class="mb-0"><i class="fas fa-comments me-2"></i>Feedback</h4>
                </div>
                <div class="card-body">
                    <?php if (isLoggedIn()):
                        $user_feedback_query = "SELECT * FROM file_feedback WHERE file_id = ? AND user_id = ?";
                        $user_feedback_stmt = mysqli_prepare($mysqli, $user_feedback_query);
                        mysqli_stmt_bind_param($user_feedback_stmt, 'ii', $file['id'], $_SESSION['user_id']);
                        mysqli_stmt_execute($user_feedback_stmt);
                        $user_feedback = mysqli_fetch_assoc(mysqli_stmt_get_result($user_feedback_stmt));
                        mysqli_stmt_close($user_feedback_stmt);

                        if (!$user_feedback): ?>
                            <form method="POST" class="mb-4">
                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <div class="rating">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>"
                                                required>
                                            <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars">
                                                <i class="fas fa-star"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Comment</label>
                                    <textarea name="comment" class="form-control bg-dark-body" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary" name="submit_feedback">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>You have already submitted feedback for this file.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="feedback-list">
                        <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                            <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                                <div class="border-bottom mb-3 pb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong><?php echo htmlspecialchars($feedback['user_name']); ?></strong>
                                            <small class="text-muted ms-2">
                                                <?php echo date('M d, Y', strtotime($feedback['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div class="text-warning">
                                            <?php echo str_repeat('★', $feedback['rating']) .
                                                str_repeat('☆', 5 - $feedback['rating']); ?>
                                        </div>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">No feedback yet. Be the first to leave a review!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mb-2">
            <div class="card shadow-sm mt-md-0 mt-lg-4">
                <div class="card-header ">
                    <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>Report File</h5>
                </div>
                <div class="card-body">
                    <?php if (isLoggedIn()): ?>
                        <form method="POST" action="view.php?slug=$slug#report" id="reportForm">
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for reporting</label>
                                <textarea name="report_reason" id="reason" class="form-control bg-dark-body" rows="3"
                                    required></textarea>
                            </div>
                            <button type="submit" name="submit_report" class="btn btn-danger">
                                <i class="fas fa-flag me-2"></i>Submit Report
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="text-muted">Please <a href="../auth/login.php">login</a> to report this file.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>