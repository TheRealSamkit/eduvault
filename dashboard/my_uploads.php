<?php
$sidebar = true;
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
if (isset($_POST['delete_file'])) {
    $file_id = mysqli_real_escape_string($mysqli, $_POST['file_id']);

    $file_query = "SELECT file_path FROM digital_files WHERE id = $file_id AND user_id = $user_id";
    $file_result = mysqli_query($mysqli, $file_query);
    $file = mysqli_fetch_assoc($file_result);

    if ($file) {
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
            flash('success', 'File deleted successfully!');
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }

        mysqli_query($mysqli, "DELETE FROM downloads WHERE file_id = $file_id");
        mysqli_query($mysqli, "DELETE FROM digital_files WHERE id = $file_id AND user_id = $user_id");
    }
}

$files_query = "SELECT f.*, s.name as subject, c.name as course, y.year as year, (SELECT COUNT(*) FROM downloads WHERE file_id = f.id) as download_count, (SELECT COUNT(*) FROM reported_content WHERE content_id = f.id) as report_count FROM digital_files f LEFT JOIN subjects s ON f.subject_id = s.id LEFT JOIN courses c ON f.course_id = c.id LEFT JOIN years y ON f.year_id = y.id WHERE f.user_id = $user_id ORDER BY f.upload_date DESC";
$files_result = mysqli_query($mysqli, $files_query);
require_once '../includes/header.php';
?>

<div class="d-flex align-items-start">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <div class="container-md mb-5 pb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-file-alt me-2"></i>My Uploads</h2>
                <a href="../files/upload.php" class="btn btn-success">
                    <i class="fas fa-upload me-2"></i>Upload New File
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (mysqli_num_rows($files_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Subject</th>
                                        <th>Course</th>
                                        <th>Year</th>
                                        <th>File Size</th>
                                        <th>Reports</th>
                                        <th>Downloads</th>
                                        <th>Upload Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($file = mysqli_fetch_assoc($files_result)): ?>
                                        <tr>
                                            <td>
                                                <i
                                                    class="fas fa-file-<?php echo getFileIcon(strtolower($file['file_type'])); ?> me-2 text-primary"></i>
                                                <a href="../files/view.php?id=<?php echo $file['id']; ?>"
                                                    class="text-decoration-none">
                                                    <?php echo htmlspecialchars($file['title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($file['subject']); ?></td>
                                            <td><?php echo htmlspecialchars($file['course']); ?></td>
                                            <td><?php echo $file['year']; ?></td>
                                            <td><?php echo formatFileSizeMB($file['file_size']); ?></td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle me-1"></i><?php echo $file['report_count']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <i class="fas fa-download me-1"></i><?php echo $file['download_count']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($file['upload_date'])); ?></td>
                                            <td>
                                                <a href="../files/view.php?id=<?php echo $file['id']; ?>"
                                                    class="btn btn-sm btn-outline-info me-1" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="../files/download.php?id=<?php echo $file['id']; ?>"
                                                    class="btn btn-sm btn-outline-primary me-1" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                                    <button type="submit" name="delete_file"
                                                        class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-file-upload fa-3x text-muted mb-3"></i>
                            <p class="lead">You haven't uploaded any files yet.</p>
                            <a href="../files/upload.php" class="btn btn-success">
                                <i class="fas fa-upload me-2"></i>Upload Your First File
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>