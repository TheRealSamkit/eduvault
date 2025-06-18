<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle file actions
if (isset($_POST['action']) && isset($_POST['file_id'])) {
    $file_id = (int) $_POST['file_id'];
    $action = $_POST['action'];

    if ($action === 'remove') {
        $file_query = mysqli_query($mysqli, "SELECT file_path FROM digital_files WHERE id = $file_id");
        $file_data = mysqli_fetch_assoc($file_query);

        if ($file_data && file_exists($file_data['file_path'])) {
            unlink($file_data['file_path']);
        }
        mysqli_query($mysqli, "DELETE FROM digital_files WHERE id = $file_id");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif ($action === 'verify') {
        mysqli_query($mysqli, "UPDATE digital_files SET verified = 1 WHERE id = $file_id");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif ($action === 'ban') {
        mysqli_query($mysqli, "UPDATE digital_files SET verified = 0 WHERE id = $file_id");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Log the action
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_query($mysqli, "INSERT INTO activity_logs (admin_id, action, ip_address) 
                          VALUES ($admin_id, 'File $action ID: $file_id', '$ip')");
}
$files = mysqli_query($mysqli, "SELECT f.*, s.name as subject, c.name as course, y.year as year, u.name as uploader_name, u.id as uploader_id, (SELECT COUNT(*) FROM downloads WHERE file_id = f.id) as download_count FROM digital_files f JOIN users u ON f.user_id = u.id LEFT JOIN subjects s ON f.subject_id = s.id LEFT JOIN courses c ON f.course_id = c.id LEFT JOIN years y ON f.year_id = y.id");
$title = "File Management - Admin Panel";
require_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-file-alt me-2"></i>File Management</h2>
                <div>
                    <button class="btn btn-success me-2" onclick="exportFiles('csv')">
                        <i class="fas fa-file-csv me-2"></i>Export CSV
                    </button>
                    <button class="btn btn-primary" onclick="exportFiles('pdf')">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="filesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Subject</th>
                                    <th>Course</th>
                                    <th>Year</th>
                                    <th>Owner</th>
                                    <th>Size</th>
                                    <th>Downloads</th>
                                    <th>Verified</th>
                                    <th>Uploaded</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($file = mysqli_fetch_assoc($files)): ?>
                                    <tr>
                                        <td><?php echo $file['id']; ?></td>
                                        <td><?php echo htmlspecialchars($file['title']); ?></td>
                                        <td>
                                            <i class="fas fa-file-<?php echo getFileIcon($file['file_type']); ?> me-1"></i>
                                            <?php echo strtoupper($file['file_type']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($file['subject']); ?></td>
                                        <td><?php echo htmlspecialchars($file['course']); ?></td>
                                        <td><?php echo htmlspecialchars($file['year']); ?></td>
                                        <td>
                                            <span data-bs-toggle="tooltip"
                                                title="<?php echo htmlspecialchars($file['uploader_id']); ?>">
                                                <?php echo htmlspecialchars($file['uploader_name']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(formatFileSizeMB($file['file_size'])); ?></td>
                                        <td><?php echo $file['download_count']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $file['verified'] ? 'success' : 'danger'; ?>">
                                                <?php echo $file['verified'] ? 'Verified' : 'Banned'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($file['upload_date'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?php echo $file['file_path']; ?>"
                                                    class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <?php if (!$file['verified']): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-success"
                                                        onclick="verifyFile(<?php echo $file['id']; ?>)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                                        onclick="banFile(<?php echo $file['id']; ?>)">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="removeFile(<?php echo $file['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php' ?>
<script>
    $(document).ready(function () {
        $('#filesTable').DataTable({
            order: [[8, 'asc']],
            pageLength: 10
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });

    function verifyFile(fileId) {
        if (confirm('Are you sure you want to verify this file?')) {
            submitFileAction(fileId, 'verify');
        }
    }

    function banFile(fileId) {
        if (confirm('Are you sure you want to ban this file?')) {
            submitFileAction(fileId, 'ban');
        }
    }

    function removeFile(fileId) {
        if (confirm('Are you sure you want to remove this file? This action cannot be undone.')) {
            submitFileAction(fileId, 'remove');
        }
    }

    function submitFileAction(fileId, action) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
                <input type="hidden" name="file_id" value="${fileId}">
                <input type="hidden" name="action" value="${action}">
            `;
        document.body.append(form);
        form.submit();
    }

    function exportFiles(format) {
        window.location.href = `exports/export.php?format=${format}&type=files`;
    }
</script>