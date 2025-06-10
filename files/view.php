<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$file_id = mysqli_real_escape_string($mysqli, $_GET['id']);

// Get file details with uploader info and download count
$query = "SELECT f.*, u.name as uploader_name, 
          (SELECT COUNT(*) FROM downloads WHERE file_id = f.id) as download_count,
          (SELECT COUNT(*) FROM reported_content WHERE content_id = f.id AND content_type = 'file') as report_count,
          (SELECT AVG(rating) FROM file_feedback WHERE file_id = f.id) as avg_rating
          FROM digital_files f 
          JOIN users u ON f.user_id = u.id 
          WHERE f.id = $file_id";
$result = mysqli_query($mysqli, $query);
$file = mysqli_fetch_assoc($result);


$feedback_query = "SELECT f.*, u.name as user_name 
                  FROM file_feedback f 
                  JOIN users u ON f.user_id = u.id 
                  WHERE f.file_id = $file_id 
                  ORDER BY f.created_at DESC";
$feedback_result = mysqli_query($mysqli, $feedback_query);

if (!$file) {
    $_SESSION['error'] = "File not found.";
    header("Location: list.php");
    exit();
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn() && $_POST['submit_feedback']) {
    $rating = mysqli_real_escape_string($mysqli, $_POST['rating']);
    $comment = mysqli_real_escape_string($mysqli, $_POST['comment']);
    $user_id = $_SESSION['user_id'];

    // Check if user has already given feedback
    $check_query = "SELECT id FROM file_feedback WHERE file_id = $file_id AND user_id = $user_id";
    $check_result = mysqli_query($mysqli, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Update existing feedback
        $update_query = "UPDATE file_feedback 
                        SET rating = $rating, comment = '$comment' 
                        WHERE file_id = $file_id AND user_id = $user_id";
        mysqli_query($mysqli, $update_query);
    } else {
        // Insert new feedback
        $insert_query = "INSERT INTO file_feedback (file_id, user_id, rating, comment) 
                        VALUES ($file_id, $user_id, $rating, '$comment')";
        mysqli_query($mysqli, $insert_query);
    }

    // Redirect to refresh the page
    header("Location: view.php?id=$file_id&feedback=success");
    exit();
}

if (isset($_POST['submit_report']) && isLoggedIn()) {
    $report_reason = mysqli_real_escape_string($mysqli, trim($_POST['report_reason']));
    $reporter_id = $_SESSION['user_id'];

    if (!empty($report_reason)) {
        $insert_report = "INSERT INTO reported_content (reporter_id, content_type, content_id, reason) 
                          VALUES ($reporter_id, 'file', $file_id, '$report_reason')";
        if (mysqli_query($mysqli, $insert_report)) {
            $_SESSION['success'] = "Thank you for your report. We'll review it soon.";
        } else {
            $_SESSION['error'] = "Failed to submit report. Please try again later.";
        }
    } else {
        $_SESSION['error'] = "Please provide a reason for your report.";
    }
    header("Location: view.php?id=$file_id#report");
    exit();
}

// Get file feedback
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

                    <small class="text-muted d-block mb-3">
                        Uploaded by <?php echo htmlspecialchars($file['uploader_name']); ?>
                        on <?php echo date('M d, Y', strtotime($file['upload_date'])); ?>
                    </small>

                    <?php if (isLoggedIn()): ?>
                        <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-success">
                            <i class="fas fa-download me-2"></i>Download File
                        </a>
                    <?php else: ?>
                        <a href="../login.php" class="btn btn-warning">
                            <i class="fas fa-lock me-2"></i>Login to Download
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Feedback Section -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h4 class="mb-0"><i class="fas fa-comments me-2"></i>Feedback</h4>
                </div>
                <div class="card-body">
                    <?php if (isLoggedIn()): ?>
                        <form method="POST" class="mb-4">
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-select" required>
                                    <option value="">Select Rating</option>
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?php echo $i; ?>">
                                            <?php echo str_repeat('★', $i) . str_repeat('☆', 5 - $i); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comment</label>
                                <textarea name="comment" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" name="submit_feedback">
                                <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                            </button>
                        </form>
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
                <div class="card-header bg-light">
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
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>Report File</h5>
                </div>
                <div class="card-body">
                    <?php if (isLoggedIn()): ?>
                        <form method="POST" action="view.php?id=<?php echo $file_id; ?>#report" id="reportForm">
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for reporting</label>
                                <textarea name="report_reason" id="reason" class="form-control" rows="3"
                                    required></textarea>
                            </div>
                            <button type="submit" name="submit_report" class="btn btn-danger">
                                <i class="fas fa-flag me-2"></i>Submit Report
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="text-muted">Please <a href="../login.php">login</a> to report this file.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php require_once '../includes/footer.php'; ?>