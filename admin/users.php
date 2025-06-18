<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $user_id = (int) $_POST['user_id'];
    $action = $_POST['action'];
    $admin_id = (int) $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

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
    header('Location: users.php?success=1');
    exit();
}

$usersQuery = "
    SELECT u.*, 
           (SELECT COUNT(*) FROM book_listings  WHERE user_id = u.id) AS books_count,
           (SELECT COUNT(*) FROM digital_files   WHERE user_id = u.id) AS files_count
      FROM users u
  ORDER BY u.created_at DESC";
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
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Location</th>
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
                                        <td><?= $user['id']; ?></td>
                                        <td><?= htmlspecialchars($user['name']); ?></td>
                                        <td><?= htmlspecialchars($user['email']); ?></td>
                                        <td><?= htmlspecialchars($user['location']); ?></td>
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
                                                    class="btn btn-sm btn-outline-primary text-primary view-btn">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($user['status'] === 'active'): ?>
                                                    <button class="btn btn-sm btn-outline-warning"
                                                        onclick="submitUserAction(<?= $user['id']; ?>, 'block')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-success"
                                                        onclick="submitUserAction(<?= $user['id']; ?>, 'unblock')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="submitUserAction(<?= $user['id']; ?>, 'delete')">
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

            <!-- Success/Error alerts -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success mt-3">Action completed successfully.</div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-danger mt-3"><?= htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

        </div>
    </div>
</div>
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userModalBody"></div>
        </div>
    </div>
</div>
<?php require_once '../includes/admin_footer.php'; ?>

<script>
    $(function () {
        $('#usersTable').DataTable({ order: [[7, 'desc']], pageLength: 10 });
    });

    function submitUserAction(id, action) {
        if (action === 'delete' && !confirm('Delete this user? This action cannot be undone.')) return;
        if (action === 'block' && !confirm('Block this user?')) return;
        if (action === 'unblock' && !confirm('Unblock this user?')) return;

        const form = $('<form>', { method: 'POST' })
            .append($('<input>', { type: 'hidden', name: 'user_id', value: id }))
            .append($('<input>', { type: 'hidden', name: 'action', value: action }));

        $('body').append(form);
        form.submit();
    }
</script>