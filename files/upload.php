<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!isset($_GET['id'])) {
    flash('error', 'Invalid request: Missing file ID.');
    header("Location: list.php");
    exit();
}

$file_id = $_GET['id'];
$query = "SELECT f.*, s.name as subject, c.name as course, y.year as year, u.name as uploader_name, u.id as uploader_id,
         (SELECT COUNT(*) FROM downloads WHERE file_id = f.id) as download_count,
         (SELECT COUNT(*) FROM reported_content WHERE content_id = f.id) as report_count,
         (SELECT AVG(rating) FROM file_feedback WHERE file_id = f.id) as avg_rating
         FROM digital_files f
         JOIN users u ON f.user_id = u.id
         LEFT JOIN subjects s ON f.subject_id = s.id
         LEFT JOIN courses c ON f.course_id = c.id
         LEFT JOIN years y ON f.year_id = y.id
         WHERE f.id = ?";
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
    flash('error', 'File not found.');
    header("Location: list.php");
    exit();
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn() && isset($_POST['submit_feedback'])) {
    $rating = (int) $_POST['rating'];
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];

    if ($rating < 1 || $rating > 5) {
        flash('error', 'Invalid rating value.');
        header("Location: view.php?id=$file_id");
        exit();
    }

    // Check if user has already given feedback
    $check_query = "SELECT id FROM file_feedback WHERE file_id = ? AND user_id = ?";
    $check_stmt = mysqli_prepare($mysqli, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'ii', $file_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        flash('error', 'You have already submitted feedback for this file.');
        header("Location: view.php?id=$file_id");
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

    header("Location: view.php?id=$file_id");
    exit();
}

// Handle report submission
if (isset($_POST['submit_report']) && isLoggedIn()) {
    $report_reason = trim($_POST['report_reason']);
    $reporter_id = $_SESSION['user_id'];

    if (!empty($report_reason)) {
        $insert_report = "INSERT INTO reported_content (reporter_id, content_type, content_id, reason) VALUES (?, 'file', ?, ?)";
        $report_stmt = mysqli_prepare($mysqli, $insert_report);
        mysqli_stmt_bind_param($report_stmt, 'iis', $reporter_id, $file_id, $report_reason);

        if (mysqli_stmt_execute($report_stmt)) {
            flash('success', "Thank you for your report. We'll review it soon.");
        } else {
            flash('error', 'Failed to submit report. Please try again later.');
        }

        mysqli_stmt_close($report_stmt);
    } else {
        flash('error', 'Please provide a reason for your report.');
    }
    header("Location: view.php?id=$file_id#report");
    exit();
}

require_once '../includes/header.php';
?>



<div class="container-fluid row justify-content-center gx-1 mb-3">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-upload me-2"></i>Upload Study Material</h4>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control bg-dark-body" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control bg-dark-body" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-select input-dark" required>
                            <option value="">Select Subject</option>
                            <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-select input-dark" required>
                            <option value="">Select Course</option>
                            <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <select name="year_id" class="form-select input-dark" required>
                            <option value="">Select Year</option>
                            <?php while ($y = mysqli_fetch_assoc($years)): ?>
                                <option value="<?php echo $y['id']; ?>"><?php echo htmlspecialchars($y['year']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File</label>
                        <input type="file" name="file" class="form-control bg-dark-body" required
                            accept="<?php echo implode(',', array_map(fn($e) => '.' . $e, $allowed_ext)); ?>">
                        <div class="form-text">Max size: 10MB. Allowed formats:
                            <?php echo strtoupper(implode(", ", $allowed_ext)) ?>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload me-2"></i>Upload File
                        </button>
                        <a href="../dashboard/my_uploads.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to My Uploads
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>