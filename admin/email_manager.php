<?php
require_once '../includes/db_connect.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    redirect('index.php');
    exit();
}

// Handle add/edit template
$template_success = '';
$template_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_template'])) {
    $key = trim($_POST['template_key']);
    $subject = trim($_POST['subject']);
    $body = trim($_POST['body']);
    $desc = trim($_POST['description']);
    $admin_id = $_SESSION['admin_id'];
    if ($key && $subject && $body) {
        // Upsert
        $stmt = mysqli_prepare($mysqli, "INSERT INTO email_templates (template_key, subject, body, description, created_by) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE subject=VALUES(subject), body=VALUES(body), description=VALUES(description), updated_at=NOW()");
        mysqli_stmt_bind_param($stmt, 'ssssi', $key, $subject, $body, $desc, $admin_id);
        if (mysqli_stmt_execute($stmt)) {
            $template_success = 'Template saved.';
        } else {
            $template_error = 'Failed to save template.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $template_error = 'All fields are required.';
    }
}
// Handle delete template
if (isset($_POST['delete_template']) && isset($_POST['template_id'])) {
    $id = (int) $_POST['template_id'];
    $stmt = mysqli_prepare($mysqli, "DELETE FROM email_templates WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        $template_success = 'Template deleted.';
    } else {
        $template_error = 'Failed to delete template.';
    }
    mysqli_stmt_close($stmt);
}
// Fetch templates
$templates = mysqli_query($mysqli, "SELECT et.*, au.username as admin_name FROM email_templates et LEFT JOIN admin_users au ON et.created_by = au.id ORDER BY et.updated_at DESC");
// Fetch logs
$logs = mysqli_query($mysqli, "SELECT el.*, au.username as admin_name FROM email_logs el LEFT JOIN admin_users au ON el.sent_by = au.id ORDER BY el.sent_at DESC LIMIT 100");
$title = 'Email Management - Admin Panel';
require_once '../includes/admin_header.php';
?>
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-envelope me-2"></i>Email Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
                    <i class="fas fa-plus me-1"></i> Add Template
                </button>
            </div>
            <?php if ($template_success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($template_success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($template_error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($template_error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Email Templates</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="templatesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Key</th>
                                    <th>Subject</th>
                                    <th>Description</th>
                                    <th>Last Updated</th>
                                    <th>By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($tpl = mysqli_fetch_assoc($templates)): ?>
                                    <tr>
                                        <td><?= $tpl['id']; ?></td>
                                        <td><?= htmlspecialchars($tpl['template_key']); ?></td>
                                        <td><?= htmlspecialchars($tpl['subject']); ?></td>
                                        <td><?= htmlspecialchars($tpl['description']); ?></td>
                                        <td><?= date('M d, Y H:i', strtotime($tpl['updated_at'])); ?></td>
                                        <td><?= htmlspecialchars($tpl['admin_name'] ?? '-'); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info"
                                                onclick="editTemplate(<?= $tpl['id']; ?>, '<?= htmlspecialchars(addslashes($tpl['template_key'])); ?>', '<?= htmlspecialchars(addslashes($tpl['subject'])); ?>', '<?= htmlspecialchars(addslashes($tpl['body'])); ?>', '<?= htmlspecialchars(addslashes($tpl['description'])); ?>')"><i
                                                    class="fas fa-edit"></i></button>
                                            <form method="POST" class="d-inline"
                                                onsubmit="return confirm('Delete this template?');">
                                                <input type="hidden" name="template_id" value="<?= $tpl['id']; ?>">
                                                <button type="submit" name="delete_template"
                                                    class="btn btn-sm btn-outline-danger"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Email Logs (last 100)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="logsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>To</th>
                                    <th>Subject</th>
                                    <th>Template</th>
                                    <th>Status</th>
                                    <th>By</th>
                                    <th>Sent At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($log = mysqli_fetch_assoc($logs)): ?>
                                    <tr>
                                        <td><?= $log['id']; ?></td>
                                        <td><?= htmlspecialchars($log['recipient']); ?></td>
                                        <td><?= htmlspecialchars($log['subject']); ?></td>
                                        <td><?= htmlspecialchars($log['template_key']); ?></td>
                                        <td><span
                                                class="badge bg-<?= $log['status'] === 'sent' ? 'success' : 'danger'; ?>"><?= ucfirst($log['status']); ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($log['admin_name'] ?? '-'); ?></td>
                                        <td><?= date('M d, Y H:i', strtotime($log['sent_at'])); ?></td>
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
<!-- Add/Edit Template Modal -->
<div class="modal fade" id="addTemplateModal" tabindex="-1" aria-labelledby="addTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTemplateModalLabel">Add/Edit Email Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Template Key</label>
                        <input type="text" class="form-control" name="template_key" id="templateKeyInput" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" id="subjectInput" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Body (HTML allowed)</label>
                        <textarea class="form-control" name="body" id="bodyInput" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" id="descInput">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_template" class="btn btn-primary">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
<script>
    $(document).ready(function () {
        $('#templatesTable').DataTable({ order: [[4, 'desc']], pageLength: 10 });
        $('#logsTable').DataTable({ order: [[6, 'desc']], pageLength: 10 });
    });
    function editTemplate(id, key, subject, body, desc) {
        $('#addTemplateModal').modal('show');
        $('#templateKeyInput').val(key);
        $('#subjectInput').val(subject);
        $('#bodyInput').val(body);
        $('#descInput').val(desc);
    }
</script>