<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

requireLogin();

$max_file_size = 10 * 1024 * 1024; // 10MB
$allowed_ext = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png'];
$allowed_mime_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'txt' => 'text/plain',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png'
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($mysqli, $_POST['title']);
    $description = mysqli_real_escape_string($mysqli, $_POST['description']);
    $subject = mysqli_real_escape_string($mysqli, $_POST['subject']);
    $course = mysqli_real_escape_string($mysqli, $_POST['course']);
    $year = (int) $_POST['year']; // sanitize as integer for security

    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            $error = "Invalid file format. Allowed: " . strtoupper(implode(', ', $allowed_ext));
        } elseif ($_FILES['file']['size'] > $max_file_size) {
            $error = "File size exceeds 10MB limit";
        } else {
            // MIME type check
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detected_mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
            finfo_close($finfo);

            if ($detected_mime !== $allowed_mime_types[$ext]) {
                $error = "File MIME type does not match the file extension.";
            } else {
                $file_path = '../uploads/files/' . uniqid() . '.' . $ext;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                    $query = "INSERT INTO digital_files (user_id, title, description, subject, course, year, file_path, file_type) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt = mysqli_prepare($mysqli, $query);
                    mysqli_stmt_bind_param($stmt, "issssiss", $user_id, $title, $description, $subject, $course, $year, $file_path, $ext);

                    if (mysqli_stmt_execute($stmt)) {
                        $_SESSION['success'] = "File uploaded successfully!";
                        header("Location:" . $_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $error = "Upload failed: " . mysqli_error($mysqli);
                        if (file_exists($file_path))
                            unlink($file_path);
                    }
                } else {
                    $error = "Failed to move uploaded file.";
                }
            }
        }
    } else {
        $error = "Please select a file to upload.";
    }

    if (!empty($error)) {
        $_SESSION['error'] = $error;
        header("Location:" . $_SERVER['PHP_SELF']);
        exit();
    }
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
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select name="course" class="form-select" required>
                            <option value="">Select Course</option>
                            <option value="B.Tech">B.Tech</option>
                            <option value="Diploma">Diploma</option>
                            <option value="UPSC">UPSC</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select" required>
                            <option value="">Select Year</option>
                            <?php for ($i = date('Y'); $i >= 2000; $i--): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File</label>
                        <input type="file" name="file" class="form-control" required
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.txt">
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