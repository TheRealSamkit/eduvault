<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $user_id = (int) $_POST['user_id'];
    $action = mysqli_real_escape_string($mysqli, $_POST['action']);
    $admin_id = (int) $_SESSION['admin_id'];
    $ip = mysqli_real_escape_string($mysqli, $_SERVER['REMOTE_ADDR'] ?? '');

    mysqli_begin_transaction($mysqli);

    $logSql = "INSERT INTO activity_logs (admin_id, id, action, ip_address)
               VALUES ($admin_id, $user_id, '$action', '$ip')";
    mysqli_query($mysqli, $logSql) or die(mysqli_error($mysqli));

    if ($action === 'block') {
        $sql = "UPDATE users SET status = 'blocked' WHERE id = $user_id";
        mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    } elseif ($action === 'unblock') {
        $sql = "UPDATE users SET status = 'active' WHERE id = $user_id";
        mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    } elseif ($action === 'delete') {
        $sql = "DELETE FROM users WHERE id = $user_id";
        mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management – Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .view-btn i {
            color: rgba(15, 47, 226, 0.8);
        }

        .view-btn:hover i {
            color: #000;
        }
    </style>
</head>

<body>
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

    <!-- User modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="userModalBody"></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

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
</body>

</html>