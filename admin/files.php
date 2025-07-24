<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    redirect("index.php");
    exit();
}

// Handle bulk and single file actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $file_ids = isset($_POST['file_ids']) ? $_POST['file_ids'] : (isset($_POST['file_id']) ? [$_POST['file_id']] : []);
    if (!is_array($file_ids))
        $file_ids = [$file_ids];
    $file_ids = array_map('intval', $file_ids);
    if ($action && $file_ids) {
        foreach ($file_ids as $file_id) {
            if ($action === 'remove') {
                $stmt = mysqli_prepare($mysqli, "SELECT file_path FROM digital_files WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'i', $file_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $file_data = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                if ($file_data && file_exists($file_data['file_path'])) {
                    unlink($file_data['file_path']);
                }
                $stmt = mysqli_prepare($mysqli, "DELETE FROM digital_files WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'i', $file_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } elseif ($action === 'verify') {
                $stmt = mysqli_prepare($mysqli, "UPDATE digital_files SET verified = 1 WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'i', $file_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } elseif ($action === 'ban') {
                $stmt = mysqli_prepare($mysqli, "UPDATE digital_files SET verified = 0 WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'i', $file_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            // Log the action
            $admin_id = $_SESSION['admin_id'];
            $ip = $_SERVER['REMOTE_ADDR'];
            $stmt = mysqli_prepare($mysqli, "INSERT INTO activity_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
            $log_action = "File $action ID: $file_id";
            mysqli_stmt_bind_param($stmt, 'iss', $admin_id, $log_action, $ip);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        redirect($_SERVER['PHP_SELF']);
        exit();
    }
    // Handle email sending
    if ($action === 'email' && !empty($_POST['email_subject']) && !empty($_POST['email_body'])) {
        $subject = $_POST['email_subject'];
        $body = $_POST['email_body'];
        $emails = [];
        foreach ($file_ids as $file_id) {
            $stmt = mysqli_prepare($mysqli, "SELECT u.email FROM digital_files f JOIN users u ON f.user_id = u.id WHERE f.id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $file_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $emails[] = $row['email'];
            }
            mysqli_stmt_close($stmt);
        }
        // Send email (simple mail, replace with PHPMailer for production)
        foreach ($emails as $to) {
            mail($to, $subject, $body, "From: admin@eduvault.local");
        }
        flash('success', 'Emails sent to selected users.');
        redirect($_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch files with new schema fields
$query = "SELECT f.*, s.name as subject, c.name as course, y.year as year, u.name as uploader_name, u.id as uploader_id, u.email as uploader_email, (SELECT COUNT(*) FROM downloads WHERE file_id = f.id) as download_count FROM digital_files f JOIN users u ON f.user_id = u.id LEFT JOIN subjects s ON f.subject_id = s.id LEFT JOIN courses c ON f.course_id = c.id LEFT JOIN years y ON f.year_id = y.id ORDER BY f.upload_date DESC";
$files = mysqli_query($mysqli, $query);
$title = "File Management - Admin Panel";
require_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-file-alt me-2"></i>File Management</h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-success" onclick="exportFiles('csv')">
                        <i class="fas fa-file-csv me-2"></i>Export CSV
                    </button>
                    <button class="btn btn-primary" onclick="exportFiles('pdf')">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </button>
                    <button class="btn btn-info" id="bulkEmailBtn">
                        <i class="fas fa-envelope me-2"></i>Email Selected
                    </button>
                </div>
            </div>
            <form method="POST" id="bulkActionForm">
                <input type="hidden" name="action" id="bulkActionInput">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-white align-middle" id="filesTable">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Subject</th>
                                        <th>Course</th>
                                        <th>Year</th>
                                        <th>Owner</th>
                                        <th>Size</th>
                                        <th>Downloads</th>
                                        <th>Visibility</th>
                                        <th>Status</th>
                                        <th>Verified</th>
                                        <th>Rating</th>
                                        <th>Uploaded</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($file = mysqli_fetch_assoc($files)): ?>
                                        <tr>
                                            <td><input type="checkbox" name="file_ids[]" value="<?php echo $file['id']; ?>">
                                            </td>
                                            <td><?php echo $file['id']; ?></td>
                                            <td><?php echo htmlspecialchars($file['title']); ?></td>
                                            <td><i
                                                    class="fas fa-file-<?php echo getFileIcon($file['file_type']); ?> me-1"></i><?php echo strtoupper($file['file_type']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($file['subject']); ?></td>
                                            <td><?php echo htmlspecialchars($file['course']); ?></td>
                                            <td><?php echo htmlspecialchars($file['year']); ?></td>
                                            <td><span data-bs-toggle="tooltip"
                                                    title="<?php echo htmlspecialchars($file['uploader_email']); ?>"><?php echo htmlspecialchars($file['uploader_name']); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars(formatFileSize($file['file_size'])); ?></td>
                                            <td><?php echo $file['download_count']; ?></td>
                                            <td><span
                                                    class="badge bg-<?php echo $file['visibility'] === 'public' ? 'info' : ($file['visibility'] === 'private' ? 'secondary' : 'warning'); ?> text-dark"><?php echo ucfirst($file['visibility']); ?></span>
                                            </td>
                                            <td><span
                                                    class="badge bg-<?php echo $file['status'] === 'active' ? 'success' : 'danger'; ?> text-dark"><?php echo ucfirst($file['status']); ?></span>
                                            </td>
                                            <td><span
                                                    class="badge bg-<?php echo $file['verified'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $file['verified'] ? 'Verified' : 'Banned'; ?> </span></td>
                                            <td><span
                                                    class="badge bg-warning text-dark"><?php echo $file['average_rating'] !== null ? number_format($file['average_rating'], 2) : 'N/A'; ?></span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($file['upload_date'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo $file['file_path']; ?>"
                                                        class="btn btn-sm btn-outline-primary" target="_blank"><i
                                                            class="fas fa-download"></i></a>
                                                    <?php if (!$file['verified']): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                            onclick="singleAction(<?php echo $file['id']; ?>, 'verify')"><i
                                                                class="fas fa-check"></i></button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                                            onclick="singleAction(<?php echo $file['id']; ?>, 'ban')"><i
                                                                class="fas fa-ban"></i></button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="singleAction(<?php echo $file['id']; ?>, 'remove')"><i
                                                            class="fas fa-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-outline-success" onclick="setBulkAction('verify')"><i
                                    class="fas fa-check me-1"></i>Verify Selected</button>
                            <button type="button" class="btn btn-outline-warning" onclick="setBulkAction('ban')"><i
                                    class="fas fa-ban me-1"></i>Ban Selected</button>
                            <button type="button" class="btn btn-outline-danger" onclick="setBulkAction('remove')"><i
                                    class="fas fa-trash me-1"></i>Delete Selected</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Send Email to Selected Users</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="email">
                    <div id="emailFileIds"></div>
                    <div class="mb-3">
                        <label for="email_subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" name="email_subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="email_body" class="form-label">Message</label>
                        <textarea class="form-control" name="email_body" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php' ?>
<script>
    $(document).ready(function () {
        $('#filesTable').DataTable({
            order: [[1, 'desc']],
            pageLength: 10
        });
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        // Select all
        $('#selectAll').on('change', function () {
            $('input[name="file_ids[]"]').prop('checked', this.checked);
        });
        // Bulk email
        $('#bulkEmailBtn').on('click', function () {
            let selected = $('input[name="file_ids[]"]:checked').map(function () { return this.value; }).get();
            if (selected.length === 0) { alert('Select at least one file.'); return; }
            let html = '';
            selected.forEach(id => {
                html += `<input type="hidden" name="file_ids[]" value="${id}">`;
            });
            $('#emailFileIds').html(html);
            var emailModal = new bootstrap.Modal(document.getElementById('emailModal'));
            emailModal.show();
        });
    });
    function setBulkAction(action) {
        let selected = $('input[name="file_ids[]"]:checked').length;
        if (selected === 0) { alert('Select at least one file.'); return; }
        $('#bulkActionInput').val(action);
        $('#bulkActionForm').submit();
    }
    function singleAction(fileId, action) {
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
        window.location.href = `export.php?format=${format}&type=files`;
    }
</script>