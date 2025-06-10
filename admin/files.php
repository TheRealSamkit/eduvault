<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle file actions
if (isset($_POST['action']) && isset($_POST['file_id'])) {
    $file_id = (int) $_POST['file_id'];
    $action = $_POST['action'];

    if ($action === 'remove') {
        // Get file path before deletion
        $file_query = mysqli_query($mysqli, "SELECT file_path FROM digital_files WHERE id = $file_id");
        $file_data = mysqli_fetch_assoc($file_query);

        if ($file_data && file_exists($file_data['file_path'])) {
            unlink($file_data['file_path']); // Delete physical file
        }

        mysqli_query($mysqli, "DELETE FROM digital_files WHERE id = $file_id");
    } elseif ($action === 'verify') {
        mysqli_query($mysqli, "UPDATE digital_files SET verified = 1 WHERE id = $file_id");
    }

    // Log the action
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_query($mysqli, "INSERT INTO activity_logs (admin_id, action, ip_address) 
                          VALUES ($admin_id, 'File $action ID: $file_id', '$ip')");
}

// Get files list with user information
$files = mysqli_query($mysqli, "SELECT f.*, u.name as owner_name, u.email as owner_email 
                               FROM digital_files f 
                               JOIN users u ON f.user_id = u.id 
                               ORDER BY f.upload_date DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body>
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
                                                <i class="fas <?php echo getFileIcon($file['file_type']); ?> me-1"></i>
                                                <?php echo strtoupper($file['file_type']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($file['subject']); ?></td>
                                            <td>
                                                <span data-bs-toggle="tooltip"
                                                    title="<?php echo htmlspecialchars($file['owner_email']); ?>">
                                                    <?php echo htmlspecialchars($file['owner_name']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatFileSize($file['file_size']); ?></td>
                                            <td><?php echo $file['download_count']; ?></td>
                                            <td>
                                                <span
                                                    class="badge bg-<?php echo $file['verified'] ? 'success' : 'warning'; ?>">
                                                    <?php echo $file['verified'] ? 'Verified' : 'Unverified'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($file['uploaded_at'])); ?></td>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#filesTable').DataTable({
                order: [[8, 'desc']],
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
            window.location.href = `export_files.php?format=${format}`;
        }
    </script>

    <?php
    function getFileIcon($fileType)
    {
        $icons = [
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word',
            'docx' => 'fa-file-word',
            'ppt' => 'fa-file-powerpoint',
            'pptx' => 'fa-file-powerpoint',
            'xls' => 'fa-file-excel',
            'xlsx' => 'fa-file-excel',
            'txt' => 'fa-file-alt',
            'zip' => 'fa-file-archive',
            'rar' => 'fa-file-archive'
        ];
        return $icons[$fileType] ?? 'fa-file';
    }

    function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 1) . ' ' . $units[$pow];
    }
    ?>
</body>

</html>