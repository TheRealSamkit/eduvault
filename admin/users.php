<?php
require_once '../includes/db_connect.php';
require_once __DIR__ . '/../vendor/autoload.php'; // PHPMailer
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// Handle bulk and single user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_ids = isset($_POST['user_ids']) ? $_POST['user_ids'] : (isset($_POST['user_id']) ? [$_POST['user_id']] : []);
    if (!is_array($user_ids))
        $user_ids = [$user_ids];
    $user_ids = array_map('intval', $user_ids);
    $admin_id = (int) $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($action && $user_ids) {
        foreach ($user_ids as $user_id) {
            mysqli_begin_transaction($mysqli);
            $logSql = "INSERT INTO activity_logs (admin_id, id, action, ip_address) VALUES (?, ?, ?, ?)";
            $log_stmt = mysqli_prepare($mysqli, $logSql);
            mysqli_stmt_bind_param($log_stmt, 'iiss', $admin_id, $user_id, $action, $ip);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
            if ($action === 'block') {
                $sql = "UPDATE users SET status = 'blocked' WHERE id = ?";
                $stmt = mysqli_prepare($mysqli, $sql);
                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } elseif ($action === 'unblock') {
                $sql = "UPDATE users SET status = 'active' WHERE id = ?";
                $stmt = mysqli_prepare($mysqli, $sql);
                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } elseif ($action === 'delete') {
                $sql = "DELETE FROM users WHERE id = ?";
                $stmt = mysqli_prepare($mysqli, $sql);
                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            mysqli_commit($mysqli);
        }
        header('Location: users.php?success=1');
        exit();
    }
    // Handle email sending
    if ($action === 'email' && !empty($_POST['email_subject']) && !empty($_POST['email_body'])) {
        $subject = $_POST['email_subject'];
        $body = $_POST['email_body'];
        $emails = [];
        foreach ($user_ids as $user_id) {
            $stmt = mysqli_prepare($mysqli, "SELECT email FROM users WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $emails[] = $row['email'];
            }
            mysqli_stmt_close($stmt);
        }
        // Use PHPMailer for sending emails
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'localhost'; // Change to your SMTP server
            $mail->Port = 25; // Change if needed
            $mail->SMTPAuth = false;
            $mail->setFrom('admin@eduvault.local', 'EduVault Admin');
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = nl2br($body);
            foreach ($emails as $to) {
                if ($to) {
                    $mail->addAddress($to);
                }
            }
            if (count($emails) > 0) {
                $mail->send();
            }
        } catch (Exception $e) {
            // Optionally log or show error
        }
        header('Location: users.php?success=1');
        exit();
    }
}

$usersQuery = "
   SELECT u.*, 
       up.preference_value AS profile_visibility,
       (SELECT COUNT(*) FROM digital_files WHERE user_id = u.id) AS files_count,
       (SELECT COUNT(*) FROM book_listings WHERE user_id = u.id) AS books_count
FROM users u
LEFT JOIN user_preferences up 
    ON u.id = up.user_id AND up.preference_key = 'activity_visibility'
ORDER BY u.created_at DESC;";
$users = mysqli_query($mysqli, $usersQuery) or die(mysqli_error($mysqli));
$title = "User Management - Admin Panel";
require_once '../includes/admin_header.php';
?>
<style>
    .view-btn i {
        color: rgba(15, 47, 226, 0.8);
    }

    .view-btn:hover i {
        color: #000;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users me-2"></i>User Management</h2>
                <button class="btn btn-info" id="bulkEmailBtn"><i class="fas fa-envelope me-2"></i>Email
                    Selected</button>
            </div>
            <form method="POST" id="bulkActionForm">
                <input type="hidden" name="action" id="bulkActionInput">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-white align-middle" id="usersTable">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Tokens</th>
                                        <th>Profile Visibility</th>
                                        <th>Books</th>
                                        <th>Files</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = mysqli_fetch_assoc($users)): ?>
                                        <tr>
                                            <td><input type="checkbox" name="user_ids[]" value="<?= $user['id']; ?>"></td>
                                            <td><?= $user['id']; ?></td>
                                            <td><?= htmlspecialchars($user['name']); ?></td>
                                            <td><?= htmlspecialchars($user['email']); ?></td>
                                            <td><span class="badge bg-info text-dark"><?= $user['tokens']; ?></span></td>
                                            <td><span
                                                    class="badge bg-<?= $user['profile_visibility'] === 'public' ? 'success' : 'secondary'; ?> text-dark"><?= ucfirst($user['profile_visibility']); ?></span>
                                            </td>
                                            <td><?= $user['books_count']; ?></td>
                                            <td><?= $user['files_count']; ?></td>
                                            <td>
                                                <span
                                                    class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                    <?= ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="../pages/view.php?id=<?= $user['id'] ?>"
                                                        class="btn btn-sm btn-outline-primary text-primary view-btn"><i
                                                            class="fas fa-eye"></i></a>
                                                    <?php if ($user['status'] === 'active'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                                            onclick="singleAction(<?= $user['id']; ?>, 'block')"><i
                                                                class="fas fa-ban"></i></button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                            onclick="singleAction(<?= $user['id']; ?>, 'unblock')"><i
                                                                class="fas fa-check"></i></button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="singleAction(<?= $user['id']; ?>, 'delete')"><i
                                                            class="fas fa-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-outline-success" onclick="setBulkAction('unblock')"><i
                                    class="fas fa-check me-1"></i>Unblock Selected</button>
                            <button type="button" class="btn btn-outline-warning" onclick="setBulkAction('block')"><i
                                    class="fas fa-ban me-1"></i>Block Selected</button>
                            <button type="button" class="btn btn-outline-danger" onclick="setBulkAction('delete')"><i
                                    class="fas fa-trash me-1"></i>Delete Selected</button>
                        </div>
                    </div>
                </div>
            </form>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success mt-3">Action completed successfully.</div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-danger mt-3"><?= htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
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
                    <div id="emailUserIds"></div>
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
<?php require_once '../includes/admin_footer.php'; ?>
<script>
    $(function () {
        $('#usersTable').DataTable({ order: [[9, 'desc']], pageLength: 10 });
        // Select all
        $('#selectAll').on('change', function () {
            $('input[name="user_ids[]"]').prop('checked', this.checked);
        });
        // Bulk email
        $('#bulkEmailBtn').on('click', function () {
            let selected = $('input[name="user_ids[]"]:checked').map(function () { return this.value; }).get();
            if (selected.length === 0) { alert('Select at least one user.'); return; }
            let html = '';
            selected.forEach(id => {
                html += `<input type="hidden" name="user_ids[]" value="${id}">`;
            });
            $('#emailUserIds').html(html);
            var emailModal = new bootstrap.Modal(document.getElementById('emailModal'));
            emailModal.show();
        });
    });
    function setBulkAction(action) {
        let selected = $('input[name="user_ids[]"]:checked').length;
        if (selected === 0) { alert('Select at least one user.'); return; }
        $('#bulkActionInput').val(action);
        $('#bulkActionForm').submit();
    }
    function singleAction(userId, action) {
        if (action === 'delete' && !confirm('Delete this user? This action cannot be undone.')) return;
        if (action === 'block' && !confirm('Block this user?')) return;
        if (action === 'unblock' && !confirm('Unblock this user?')) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
                <input type="hidden" name="user_id" value="${userId}">
                <input type="hidden" name="action" value="${action}">
            `;
        document.body.append(form);
        form.submit();
    }
</script>