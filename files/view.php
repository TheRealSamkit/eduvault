<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!isset($_GET['id'])) {
    redirect("list.php");
    exit();
}

$file_id = $_GET['id'];
$query = "SELECT f.*, s.name as subject, c.name as course, y.year as year, u.name as uploader_name, u.id as uploader_id, (SELECT COUNT(*) FROM downloads WHERE file_id = f.id) as download_count, (SELECT COUNT(*) FROM reported_content WHERE content_id = f.id) as report_count, (SELECT AVG(rating) FROM file_feedback WHERE file_id = f.id) as avg_rating FROM digital_files f JOIN users u ON f.user_id = u.id LEFT JOIN subjects s ON f.subject_id = s.id LEFT JOIN courses c ON f.course_id = c.id LEFT JOIN years y ON f.year_id = y.id WHERE f.id = ?";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $file_id);
mysqli_stmt_execute($stmt);
$file = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$feedback_query = "SELECT f.*, u.name as user_name
                  FROM file_feedback f 
                  JOIN users u ON f.user_id = u.id 
                  WHERE f.file_id = ? 
                  ORDER BY f.created_at DESC";
$feedback_stmt = mysqli_prepare($mysqli, $feedback_query);
mysqli_stmt_bind_param($feedback_stmt, 'i', $file_id);
mysqli_stmt_execute($feedback_stmt);
$feedback_result = mysqli_stmt_get_result($feedback_stmt);

if (!$file) {
    flash("error", 'File not found or does not exist.');
    redirect("list.php");
    exit();
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn() && isset($_POST['submit_feedback'])) {
    $rating = (int) $_POST['rating'];
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        flash('error', 'Invalid rating value. Please select a rating between 1 and 5.');
        redirect("view.php?id=$file_id");
        exit();
    }

    // Check if user has already given feedback
    $check_query = "SELECT id FROM file_feedback WHERE file_id = ? AND user_id = ?";
    $check_stmt = mysqli_prepare($mysqli, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'ii', $file_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        flash('info', 'You have already submitted feedback for this file.');
        redirect("view.php?id=$file_id");
        exit();
    }

    // Insert new feedback
    $insert_query = "INSERT INTO file_feedback (file_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
    $insert_stmt = mysqli_prepare($mysqli, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, 'iiis', $file_id, $user_id, $rating, $comment);

    if (mysqli_stmt_execute($insert_stmt)) {
        flash('success', 'Thank you for your feedback!');
    } else {
        flash('error', 'Failed to submit feedback. Please try again later.');
    }

    mysqli_stmt_close($insert_stmt);
    mysqli_stmt_close($check_stmt);

    // Redirect to refresh the page
    redirect("view.php?id=$file_id");
    exit();
}

if (isset($_POST['submit_report']) && isLoggedIn()) {
    $report_reason = trim($_POST['report_reason']);
    $reporter_id = $_SESSION['user_id'];

    if (!empty($report_reason)) {
        $insert_report = "INSERT INTO reported_content (reporter_id, content_type, content_id, reason) VALUES (?, 'file', ?, ?)";
        $report_stmt = mysqli_prepare($mysqli, $insert_report);
        mysqli_stmt_bind_param($report_stmt, 'iis', $reporter_id, $file_id, $report_reason);
        if (mysqli_stmt_execute($report_stmt)) {
            flash('success', 'Report submitted successfully. Thank you for helping us keep the platform safe.');
        } else {
            flash('error', 'Failed to submit report. Please try again later.');
        }
        mysqli_stmt_close($report_stmt);
    } else {
        flash('error', 'Report reason cannot be empty.');
    }
    redirect("view.php?id=$file_id#report");
    exit();
}
require_once '../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h3 class="card-title">
                            <i
                                class="fas fa-file-<?php echo getFileIcon(strtolower($file['file_type'])); ?> me-2 text-primary"></i>
                            <?php echo htmlspecialchars($file['title']); ?>
                        </h3>
                        <div>
                            <span class="badge bg-info me-2">
                                <i class="fas fa-download me-1"></i><?php echo $file['download_count']; ?> Downloads
                            </span>
                            <span class="badge bg-warning">
                                <i class="fas fa-star me-1"></i>
                                <?php echo number_format($file['avg_rating'], 1); ?>
                            </span>
                        </div>
                    </div>

                    <p class="card-text"><?php echo nl2br(htmlspecialchars($file['description'])); ?></p>

                    <div class="mb-3">
                        <span class="badge bg-primary me-2">
                            <i class="fas fa-graduation-cap me-1"></i><?php echo htmlspecialchars($file['subject']); ?>
                        </span>
                        <span class="badge bg-secondary me-2">
                            <i class="fas fa-book me-1"></i><?php echo htmlspecialchars($file['course']); ?>
                        </span>
                        <span class="badge bg-success">
                            <i class="fas fa-calendar me-1"></i><?php echo $file['year']; ?>
                        </span>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="fas fa-file me-1"></i>
                            <?php echo formatFileSize($file['file_size']); ?>
                        </small>
                    </div>

                    <small class="text-muted d-block mb-3">
                        <?php if (isLoggedIn()): ?>
                            Uploaded by <a href="../pages/view.php?id=<?php echo $file['uploader_id']; ?>">
                                <?php echo htmlspecialchars($file['uploader_name']); ?>
                            </a>
                            on <?php echo date('M d, Y', strtotime($file['upload_date'])); ?>
                        <?php else: ?>
                            Uploaded by <?php echo htmlspecialchars($file['uploader_name']); ?>
                            on <?php echo date('M d, Y', strtotime($file['upload_date'])); ?>
                        <?php endif; ?>
                    </small>

                    <?php if (isLoggedIn()): ?>
                        <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-success">
                            <i class="fas fa-download me-2"></i>Download File
                        </a>
                    <?php else: ?>
                        <a href="../auth/login.php" class="btn btn-warning">
                            <i class="fas fa-lock me-2"></i>Login to Download
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header ">
                    <h4 class="mb-0"><i class="fas fa-comments me-2"></i>Feedback</h4>
                </div>
                <div class="card-body">
                    <?php if (isLoggedIn()):
                        // Check if user has already given feedback
                        $user_feedback_query = "SELECT * FROM file_feedback WHERE file_id = ? AND user_id = ?";
                        $user_feedback_stmt = mysqli_prepare($mysqli, $user_feedback_query);
                        mysqli_stmt_bind_param($user_feedback_stmt, 'ii', $file_id, $_SESSION['user_id']);
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

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header ">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>File Information</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-file me-2 text-primary"></i>
                            Type: <?php echo strtoupper($file['file_type']); ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-download me-2 text-success"></i>
                            Downloads: <?php echo $file['download_count']; ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-star me-2 text-warning"></i>
                            Rating: <?php echo number_format($file['avg_rating'], 1); ?>/5.0
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-calendar-alt me-2 text-info"></i>
                            Uploaded: <?php echo date('F d, Y', strtotime($file['upload_date'])); ?>
                        </li>
                        </li>
                        <li>
                            <i class="fas fa-times-circle me-2 text-danger"></i>
                            Reports: <?php echo $file['report_count']; ?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card shadow-sm mt-4">
                <div class="card-header ">
                    <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>Report File</h5>
                </div>
                <div class="card-body">
                    <?php if (isLoggedIn()): ?>
                        <form method="POST" action="view.php?id=<?php echo $file_id; ?>#report" id="reportForm">
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
</div>
<?php require_once '../includes/footer.php'; ?>