<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($mysqli, 'utf8mb4');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $user_id = (int) $_POST['user_id'];
    $action = $_POST['action'];
    $admin_id = (int) $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    try {
        mysqli_begin_transaction($mysqli);

        $logSql = "INSERT INTO activity_logs (admin_id, id, action, ip_address)
                    VALUES (?, ?, ?, ?)";
        $logStmt = mysqli_prepare($mysqli, $logSql);
        mysqli_stmt_bind_param($logStmt, 'iiss', $admin_id, $user_id, $action, $ip);
        mysqli_stmt_execute($logStmt);

        if ($action === 'block') {
            $stmt = mysqli_prepare($mysqli, 'UPDATE users SET status = \"blocked\" WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
        } elseif ($action === 'unblock') {
            $stmt = mysqli_prepare($mysqli, 'UPDATE users SET status = \"active\"  WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
        } elseif ($action === 'delete') {
            $stmt = mysqli_prepare($mysqli, 'DELETE FROM users WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
        }

        mysqli_commit($mysqli);
        header('Location: users.php?success=1'); // PRG pattern – prevent resubmission
        exit();
    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($mysqli);
        error_log('MySQL Error: ' . $e->getMessage());
        header('Location: users.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}

$users = mysqli_query(
    $mysqli,
    "SELECT u.*, 
            (SELECT COUNT(*) FROM book_listings  WHERE user_id = u.id) AS books_count,
            (SELECT COUNT(*) FROM digital_files   WHERE user_id = u.id) AS files_count
       FROM users u
   ORDER BY u.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management – Admin Panel</title>

    <!-- Bootstrap & DataTables CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>

            <!-- Main content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-users me-2"></i>User Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="fas fa-download me-2"></i>Export Users
                    </button>
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
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        onclick="viewUser(<?= $user['id']; ?>)"><i
                                                            class="fas fa-eye"></i></button>

                                                    <?php if ($user['status'] === 'active'): ?>
                                                        <button class="btn btn-sm btn-outline-warning"
                                                            onclick="submitUserAction(<?= $user['id']; ?>, 'block')"><i
                                                                class="fas fa-ban"></i></button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-success"
                                                            onclick="submitUserAction(<?= $user['id']; ?>, 'unblock')"><i
                                                                class="fas fa-check"></i></button>
                                                    <?php endif; ?>

                                                    <button class="btn btn-sm btn-outline-danger"
                                                        onclick="submitUserAction(<?= $user['id']; ?>, 'delete')"><i
                                                            class="fas fa-trash"></i></button>
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

    <!-- User modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="userModalBody"><!-- populated by AJAX --></div>
            </div>
        </div>
    </div>
    <!-- 
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success text-center m-2">Action completed successfully.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center m-2">There was a problem completing the action.</div>
        <?php endif; ?> -->

    <!-- JS assets -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(function () {
            $('#usersTable').DataTable({
                order: [[7, 'desc']],  // sort by Joined (8th column index starts at 0)
                pageLength: 10
            });
        });

        function viewUser(id) {
            $.get('ajax/get_user.php', { id }, data => {
                $('#userModalBody').html(data);
                $('#userModal').modal('show');
            });
        }

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
</body>

</html>