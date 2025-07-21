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

$host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];

require_once '../includes/header.php';
?>

<div class="d-flex align-items-start">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <div class="container-md">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-file-alt me-2"></i>My Uploads</h2>
                <div>
                    <button id="toggleUploadsViewBtn" class="btn btn-outline-secondary me-2"><i class="fas fa-th"></i>
                        View</button>
                    <a href="../files/upload.php" class="btn btn-success">
                        <i class="fas fa-upload me-2"></i>Upload New File
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (mysqli_num_rows($files_result) > 0): ?>
                        <div id="uploadsTable" class="table-responsive">
                            <table class="table uploads-table align-middle table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Subject</th>
                                        <th>Course</th>
                                        <th>Year</th>
                                        <th>Tag</th>
                                        <th>Status</th>
                                        <th>Size</th>
                                        <th>Stats</th>
                                        <th>Hash</th>
                                        <th>Keywords</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php mysqli_data_seek($files_result, 0);
                                    while ($file = mysqli_fetch_assoc($files_result)): ?>
                                        <?php $preview = generateFilePreview($file); ?>
                                        <tr>
                                            <td>
                                                <i
                                                    class="fas fa-file-<?php echo getFileIcon(strtolower($file['file_type'])); ?> me-2 text-primary"></i>
                                                <a href="../files/view.php?slug=<?php echo $file['slug']; ?>"
                                                    class="text-decoration-none fw-semibold text-body">
                                                    <?php echo htmlspecialchars($file['title']); ?>
                                                </a>
                                            </td>
                                            <td class="text-body"><?php echo htmlspecialchars($file['subject']); ?></td>
                                            <td class="text-body"><?php echo htmlspecialchars($file['course']); ?></td>
                                            <td class="text-body"><?php echo $file['year']; ?></td>
                                            <td>
                                                <?php $all_tags = array_filter(array_map('trim', explode(',', $file['tags']))); ?>
                                                <?php if (!empty($all_tags)): ?>
                                                    <span class="badge bg-body-secondary text-body" data-bs-toggle="tooltip"
                                                        title="<?php echo htmlspecialchars(implode(', ', $all_tags)); ?>">
                                                        <?php echo htmlspecialchars($all_tags[0]); ?>
                                                        <?php if (count($all_tags) > 1): ?>
                                                            +<?php echo count($all_tags) - 1; ?><?php endif; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    <span
                                                        class="badge bg-<?php echo $file['status'] === 'active' ? 'success' : 'secondary'; ?> text-body">
                                                        <?php echo ucfirst($file['status'] ?? 'N/A'); ?> </span>
                                                    <span
                                                        class="badge bg-<?php echo $file['visibility'] === 'public' ? 'info' : 'secondary'; ?> text-body">
                                                        <?php echo ucfirst($file['visibility'] ?? 'N/A'); ?> </span>
                                                    <span
                                                        class="badge bg-<?php echo $file['verified'] ? 'success' : 'danger'; ?> text-body">
                                                        <?php echo $file['verified'] ? 'Verified' : 'Unverified'; ?> </span>
                                                </div>
                                            </td>
                                            <td class="text-body"><?php echo formatFileSize($file['file_size']); ?></td>
                                            <td>
                                                <span class="me-2 text-body" data-bs-toggle="tooltip" title="Downloads">
                                                    <i class="fas fa-download text-info"></i>
                                                    <?php echo $file['download_count']; ?>
                                                </span>
                                                <span class="me-2 text-body" data-bs-toggle="tooltip" title="Reports">
                                                    <i class="fas fa-flag text-danger"></i> <?php echo $file['report_count']; ?>
                                                </span>
                                                <span class="text-body" data-bs-toggle="tooltip" title="Rating">
                                                    <i class="fas fa-star text-warning"></i>
                                                    <?php echo number_format($file['average_rating'] ?? 0, 1); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-body" data-bs-toggle="tooltip"
                                                    title="<?php echo htmlspecialchars($file['content_hash']); ?>">
                                                    <?php echo htmlspecialchars(substr($file['content_hash'], 0, 8)); ?>...
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-body" data-bs-toggle="tooltip"
                                                    title="<?php echo htmlspecialchars($file['keywords']); ?>">
                                                    <?php echo htmlspecialchars(substr($file['keywords'], 0, 16)); ?>...
                                                </span>
                                            </td>
                                            <td class="text-body"><?php echo date('M d, Y', strtotime($file['upload_date'])); ?>
                                            </td>
                                            <td class="d-flex gap-1">
                                                <?php if ($preview): ?>
                                                    <?php if ($preview['type'] === 'pdf' || $preview['type'] === 'text'): ?>
                                                        <a href="<?php echo $preview['url']; ?>" target="_blank"
                                                            class="btn btn-outline-secondary action-btn" data-bs-toggle="tooltip"
                                                            title="Full Preview">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    <?php elseif ($preview['type'] === 'image'): ?>
                                                        <button type="button"
                                                            class="btn btn-outline-secondary action-btn btn-preview-file"
                                                            data-bs-toggle="tooltip" title="Preview"
                                                            data-file-slug="<?php echo urlencode($file['slug']); ?>"
                                                            data-file-type="<?php echo strtolower($file['file_type']); ?>"
                                                            data-file-title="<?php echo htmlspecialchars($file['title']); ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <a href="../files/download.php?slug=<?php echo $file['slug']; ?>"
                                                    class="btn btn-outline-primary action-btn" data-bs-toggle="tooltip"
                                                    title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a href="../files/view.php?slug=<?php echo $file['slug']; ?>"
                                                    class="btn btn-outline-info action-btn" data-bs-toggle="tooltip"
                                                    title="View Details">
                                                    <i class="fas fa-info-circle"></i>
                                                </a>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                                    <button type="submit" name="delete_file"
                                                        class="btn btn-outline-danger action-btn" data-bs-toggle="tooltip"
                                                        title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="uploadsGrid" class="row g-4">
                            <?php mysqli_data_seek($files_result, 0);
                            while ($file = mysqli_fetch_assoc($files_result)): ?>
                                <?php $preview = generateFilePreview($file); ?>
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="card h-100">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0 text-truncate">
                                                    <a href="../files/view.php?slug=<?php echo $file['slug']; ?>"
                                                        class="text-decoration-none fw-semibold text-body">
                                                        <i
                                                            class="fas fa-file-<?php echo getFileIcon(strtolower($file['file_type'])); ?> me-2 text-primary"></i>
                                                        <?php echo htmlspecialchars($file['title']); ?>
                                                    </a>
                                                </h6>
                                            </div>
                                            <div class="mb-2 small text-muted">
                                                <?php echo htmlspecialchars($file['subject']); ?> |
                                                <?php echo htmlspecialchars($file['course']); ?> | <?php echo $file['year']; ?>
                                            </div>
                                            <div class="mb-2">
                                                <?php $all_tags = array_filter(array_map('trim', explode(',', $file['tags']))); ?>
                                                <?php if (!empty($all_tags)): ?>
                                                    <span class="badge bg-body-secondary text-body" data-bs-toggle="tooltip"
                                                        title="<?php echo htmlspecialchars(implode(', ', $all_tags)); ?>">
                                                        <?php echo htmlspecialchars($all_tags[0]); ?>
                                                        <?php if (count($all_tags) > 1): ?>+<?php echo count($all_tags) - 1; ?><?php endif; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex gap-2 mb-2 flex-wrap">
                                                <span class="badge bg-info"><i
                                                        class="fas fa-download me-1"></i><?php echo $file['download_count']; ?></span>
                                                <span class="badge bg-warning"><i
                                                        class="fas fa-star me-1"></i><?php echo number_format($file['average_rating'] ?? 0, 1); ?></span>
                                                <span class="badge bg-light text-dark"><i
                                                        class="fas fa-hdd me-1"></i><?php echo formatFileSize($file['file_size']); ?></span>
                                            </div>
                                            <div class="d-flex gap-1 mt-auto">
                                                <a href="../files/view.php?slug=<?php echo $file['slug']; ?>"
                                                    class="btn btn-outline-info btn-sm flex-fill" title="View Details"><i
                                                        class="fas fa-info-circle"></i></a>
                                                <a href="../files/download.php?slug=<?php echo $file['slug']; ?>"
                                                    class="btn btn-outline-primary btn-sm flex-fill" title="Download"><i
                                                        class="fas fa-download"></i></a>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                                    <button type="submit" name="delete_file"
                                                        class="btn btn-outline-danger btn-sm flex-fill" title="Delete"><i
                                                            class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
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
<?php require_once '../modals/filePreviewModal.php'; ?>