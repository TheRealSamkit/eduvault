<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    redirect("index.php");
    exit();
}

// Handle bulk and single report actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $report_ids = isset($_POST['report_ids']) ? $_POST['report_ids'] : (isset($_POST['report_id']) ? [$_POST['report_id']] : []);
    if (!is_array($report_ids))
        $report_ids = [$report_ids];
    $report_ids = array_map('intval', $report_ids);
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $resolution_notes = isset($_POST['notes']) ? mysqli_real_escape_string($mysqli, $_POST['notes']) : '';
    if ($action && $report_ids) {
        foreach ($report_ids as $report_id) {
            if (in_array($action, ['resolved', 'dismissed'])) {
                // Get report info before updating
                $stmt = mysqli_prepare($mysqli, "SELECT reporter_id, content_type, content_id FROM reported_content WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'i', $report_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $report_data = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                $stmt = mysqli_prepare($mysqli, "UPDATE reported_content SET status = ?, admin_id = ?, resolution_notes = ?, resolved_at = NOW() WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'sisi', $action, $admin_id, $resolution_notes, $report_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $stmt = mysqli_prepare($mysqli, "INSERT INTO activity_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                $log_action = "Report $action ID: $report_id";
                mysqli_stmt_bind_param($stmt, 'iss', $admin_id, $log_action, $ip);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                // Send notification to reporter if they have an account
                if ($report_data && $report_data['reporter_id']) {
                    $title = "Report Resolved";
                    $message = "Your report on {$report_data['content_type']} (ID: {$report_data['content_id']}) has been marked as {$action}.";
                    createNotification($report_data['reporter_id'], 'report_resolved', $title, $message, null, null, $mysqli);
                }
            }
        }
        redirect($_SERVER['PHP_SELF']);
        exit();
    }
    // Handle email sending
    if ($action === 'email' && !empty($_POST['email_subject']) && !empty($_POST['email_body'])) {
        $subject = $_POST['email_subject'];
        $body = $_POST['email_body'];
        $emails = [];
        foreach ($report_ids as $report_id) {
            $stmt = mysqli_prepare($mysqli, "SELECT u.email FROM reported_content rc LEFT JOIN users u ON rc.reporter_id = u.id WHERE rc.id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $report_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $emails[] = $row['email'];
            }
            mysqli_stmt_close($stmt);
        }
        foreach ($emails as $to) {
            if ($to)
                mail($to, $subject, $body, "From: admin@eduvault.local");
        }
        redirect($_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch all reports with new schema fields
$query = "SELECT rc.*, u.name AS reporter_name, u.email AS reporter_email FROM reported_content rc LEFT JOIN users u ON rc.reporter_id = u.id ORDER BY rc.created_at DESC";
$reports = mysqli_query($mysqli, $query);
$title = "Reported Content - Admin Panel";
require_once '../includes/admin_header.php';
?>
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-flag me-2"></i>Reported Content</h2>
                <button class="btn btn-info" id="bulkEmailBtn"><i class="fas fa-envelope me-2"></i>Email
                    Selected</button>
            </div>
            <form method="POST" id="bulkActionForm">
                <input type="hidden" name="action" id="bulkActionInput">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-white align-middle" id="reportsTable">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Content ID</th>
                                        <th>Reporter</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Admin</th>
                                        <th>Notes</th>
                                        <th>Resolved At</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($report = mysqli_fetch_assoc($reports)): ?>
                                        <tr>
                                            <td><input type="checkbox" name="report_ids[]"
                                                    value="<?php echo $report['id']; ?>"></td>
                                            <td><?php echo $report['id']; ?></td>
                                            <td class="text-uppercase"><?php echo $report['content_type']; ?></td>
                                            <td><?php echo $report['content_id']; ?></td>
                                            <td>
                                                <?php if ($report['reporter_name']): ?>
                                                    <span data-bs-toggle="tooltip"
                                                        title="<?php echo htmlspecialchars($report['reporter_email']); ?>">
                                                        <?php echo htmlspecialchars($report['reporter_name']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <em>Guest</em>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo nl2br(htmlspecialchars($report['reason'])); ?></td>
                                            <td><span
                                                    class="badge bg-<?php echo $report['status'] === 'pending' ? 'warning' : ($report['status'] === 'resolved' ? 'success' : 'secondary'); ?>">
                                                    <?php echo ucfirst($report['status']); ?> </span></td>
                                            <td><?php echo $report['admin_id'] ? htmlspecialchars($report['admin_id']) : '-'; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($report['resolution_notes']); ?></td>
                                            <td><?php echo $report['resolved_at'] ? date('M d, Y H:i', strtotime($report['resolved_at'])) : '-'; ?>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?></td>
                                            <td>
                                                <?php if ($report['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-sm btn-success me-1"
                                                        onclick="handleReport(<?php echo $report['id']; ?>, 'resolved')"><i
                                                            class="fas fa-check"></i> Resolve</button>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="handleReport(<?php echo $report['id']; ?>, 'dismissed')"><i
                                                            class="fas fa-times"></i> Dismiss</button>
                                                <?php else: ?>
                                                    <em>No actions</em>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-outline-success" onclick="setBulkAction('resolved')"><i
                                    class="fas fa-check me-1"></i>Resolve Selected</button>
                            <button type="button" class="btn btn-outline-danger" onclick="setBulkAction('dismissed')"><i
                                    class="fas fa-times me-1"></i>Dismiss Selected</button>
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
                    <h5 class="modal-title" id="emailModalLabel">Send Email to Selected Reporters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="email">
                    <div id="emailReportIds"></div>
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
        $('#reportsTable').DataTable({
            order: [[10, 'desc']],
            pageLength: 10
        });
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        // Select all
        $('#selectAll').on('change', function () {
            $('input[name="report_ids[]"]').prop('checked', this.checked);
        });
        // Bulk email
        $('#bulkEmailBtn').on('click', function () {
            let selected = $('input[name="report_ids[]"]:checked').map(function () { return this.value; }).get();
            if (selected.length === 0) { alert('Select at least one report.'); return; }
            let html = '';
            selected.forEach(id => {
                html += `<input type="hidden" name="report_ids[]" value="${id}">`;
            });
            $('#emailReportIds').html(html);
            var emailModal = new bootstrap.Modal(document.getElementById('emailModal'));
            emailModal.show();
        });
    });
    function setBulkAction(action) {
        let selected = $('input[name="report_ids[]"]:checked').length;
        if (selected === 0) { alert('Select at least one report.'); return; }
        $('#bulkActionInput').val(action);
        $('#bulkActionForm').submit();
    }
    function handleReport(reportId, action) {
        const notes = prompt("Enter resolution notes (optional):");
        if (confirm(`Are you sure you want to mark this report as "${action}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="report_id" value="${reportId}">
                <input type="hidden" name="action" value="${action}">
                <input type="hidden" name="notes" value="${notes ? notes.replace(/"/g, '&quot;') : ''}">`;
            document.body.append(form);
            form.submit();
        }
    }
</script>