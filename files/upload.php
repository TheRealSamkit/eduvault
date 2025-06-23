<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

$sidebar = true;
requireLogin();

$max_file_size = 10 * 1024 * 1024;
$allowed_mimes = getAllMimes($mysqli);
$allowed_ext = array_keys($allowed_mimes);
$allowed_mime_types = $allowed_mimes;

$error = '';

$subjects = mysqli_query($mysqli, "SELECT id, name FROM subjects ORDER BY name ASC");
$courses = mysqli_query($mysqli, "SELECT id, name FROM courses ORDER BY name ASC");
$years = mysqli_query($mysqli, "SELECT id, year FROM years ORDER BY year DESC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($mysqli, $_POST['title']);
    $description = mysqli_real_escape_string($mysqli, $_POST['description']);
    $subject_id = (int) $_POST['subject_id'];
    $course_id = (int) $_POST['course_id'];
    $year_id = (int) $_POST['year_id'];

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            $error = "Invalid file format. Allowed: " . strtoupper(implode(', ', $allowed_ext));
        } elseif ($_FILES['file']['size'] > $max_file_size) {
            $error = "File size exceeds 10MB limit";
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detected_mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
            finfo_close($finfo);

            if ($detected_mime !== $allowed_mime_types[$ext]) {
                $error = "File MIME type does not match the file extension.";
            } else {
                $file_size = round($_FILES['file']['size'] / 1000000, 1);
                $file_path = '../uploads/files/' . uniqid() . '.' . $ext;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                    $query = "INSERT INTO digital_files (user_id, title, description, subject_id, course_id, year_id, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt = mysqli_prepare($mysqli, $query);
                    mysqli_stmt_bind_param($stmt, "issiiisss", $user_id, $title, $description, $subject_id, $course_id, $year_id, $file_path, $ext, $file_size);

                    if (mysqli_stmt_execute($stmt)) {
                        flash('success', 'File uploaded successfully!');
                        redirect("" . $_SERVER['PHP_SELF']);
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
        flash('error', $error);
        redirect("" . $_SERVER['PHP_SELF']);
        exit();
    }
}
require_once '../includes/header.php';
?>



<div class="d-flex align-items-start">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
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
                                <textarea name="description" class="form-control bg-dark-body" rows="3"
                                    required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <select name="subject_id" class="form-select input-dark" required>
                                    <option value="">Select Subject</option>
                                    <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
                                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Course</label>
                                <select name="course_id" class="form-select input-dark" required>
                                    <option value="">Select Course</option>
                                    <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Year</label>
                                <select name="year_id" class="form-select input-dark" required>
                                    <option value="">Select Year</option>
                                    <?php while ($y = mysqli_fetch_assoc($years)): ?>
                                        <option value="<?php echo $y['id']; ?>"><?php echo htmlspecialchars($y['year']); ?>
                                        </option>
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
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>