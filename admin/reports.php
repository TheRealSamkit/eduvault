<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    redirect("index.php");
    exit();
}

// Handle report actions
if (isset($_POST['action'], $_POST['report_id'])) {
    $report_id = (int) $_POST['report_id'];
    $action = $_POST['action'];
    $resolution_notes = isset($_POST['notes']) ? mysqli_real_escape_string($mysqli, $_POST['notes']) : '';
    $admin_id = $_SESSION['admin_id'];

    if (in_array($action, ['resolved', 'dismissed'])) {
        // Get report info before updating
        $report_query = mysqli_query($mysqli, "
            SELECT reporter_id, content_type, content_id 
            FROM reported_content 
            WHERE id = $report_id
        ");
        $report_data = mysqli_fetch_assoc($report_query);

        mysqli_query($mysqli, "
            UPDATE reported_content 
            SET status = '$action', admin_id = $admin_id, resolution_notes = '$resolution_notes', resolved_at = NOW()
            WHERE id = $report_id
        ");
        mysqli_query($mysqli, "
            INSERT INTO activity_logs (admin_id, action, ip_address) 
            VALUES ($admin_id, 'Report $action ID: $report_id', '{$_SERVER['REMOTE_ADDR']}')
        ");

        // Send notification to reporter if they have an account
        if ($report_data && $report_data['reporter_id']) {
            $title = "Report Resolved";
            $message = "Your report on {$report_data['content_type']} (ID: {$report_data['content_id']}) has been marked as {$action}.";
            createNotification($report_data['reporter_id'], 'report_resolved', $title, $message, null, null, $mysqli);
        }

        redirect("" . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch all reports
$reports = mysqli_query($mysqli, "
    SELECT rc.*, u.name AS reporter_name, u.email AS reporter_email 
    FROM reported_content rc
    LEFT JOIN users u ON rc.reporter_id = u.id
    ORDER BY rc.created_at DESC
");
$tittle = "Reported Content - Admin Panel";
require_once '../includes/admin_header.php';
?>
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-flag me-2"></i>Reported Content</h2>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="reportsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Content ID</th>
                                    <th>Reporter</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($report = mysqli_fetch_assoc($reports)): ?>
                                    <tr>
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
                                        <td>
                                            <span class="badge bg-<?php
                                            echo $report['status'] === 'pending' ? 'warning' :
                                                ($report['status'] === 'resolved' ? 'success' : 'secondary');
                                            ?>">
                                                <?php echo ucfirst($report['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?></td>
                                        <td>
                                            <?php if ($report['status'] === 'pending'): ?>
                                                <button class="btn btn-sm btn-success me-1"
                                                    onclick="handleReport(<?php echo $report['id']; ?>, 'resolved')">
                                                    <i class="fas fa-check"></i> Resolve
                                                </button>
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="handleReport(<?php echo $report['id']; ?>, 'dismissed')">
                                                    <i class="fas fa-times"></i> Dismiss
                                                </button>
                                            <?php else: ?>
                                                <em>No actions</em>
                                            <?php endif; ?>
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
        $('#reportsTable').DataTable({
            order: [[6, 'desc']],
            pageLength: 10
        });
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });

    function handleReport(reportId, action) {
        const notes = prompt("Enter resolution notes (optional):");
        if (confirm(`Are you sure you want to mark this report as "${action}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="report_id" value="${reportId}">
                <input type="hidden" name="action" value="${action}">
                <input type="hidden" name="notes" value="${notes ? notes.replace(/"/g, '&quot;') : ''}">
            `;
            document.body.append(form);
            form.submit();
        }
    }
</script>