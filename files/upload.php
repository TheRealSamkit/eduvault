<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/header.php';

requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($mysqli, $_POST['title']);
    $description = mysqli_real_escape_string($mysqli, $_POST['description']);
    $subject = mysqli_real_escape_string($mysqli, $_POST['subject']);
    $course = mysqli_real_escape_string($mysqli, $_POST['course']);
    $year = mysqli_real_escape_string($mysqli, $_POST['year']);

    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $file_path = '../uploads/files/' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                $query = "INSERT INTO digital_files (user_id, title, description, subject, course, year, file_path, file_type) 
                         VALUES ($user_id, '$title', '$description', '$subject', '$course', $year, '$file_path', '$ext')";

                if (mysqli_query($mysqli, $query)) {
                    $success = "File uploaded successfully!";
                } else {
                    $error = "Upload failed: " . mysqli_error($mysqli);
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
            } else {
                $error = "Failed to move uploaded file";
            }
        } else {
            $error = "Invalid file format. Allowed: PDF, DOC, DOCX, PPT, PPTX";
        }
    } else {
        $error = "Please select a file to upload";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-upload me-2"></i>Upload Study Material</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

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
                            accept=".pdf,.doc,.docx,.ppt,.pptx">
                        <div class="form-text">Max size: 10MB. Allowed formats: PDF, DOC, DOCX, PPT, PPTX</div>
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