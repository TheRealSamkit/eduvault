<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle user actions (block/unblock)
if (isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = (int) $_POST['user_id'];
    $action = $_POST['action'];

    if ($action === 'block') {
        mysqli_query($mysqli, "UPDATE users SET status = 'blocked' WHERE id = $user_id");
    } elseif ($action === 'unblock') {
        mysqli_query($mysqli, "UPDATE users SET status = 'active' WHERE id = $user_id");
    } elseif ($action === 'delete') {
        mysqli_query($mysqli, "DELETE FROM users WHERE id = $user_id");
    }

    // Log the action
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_query($mysqli, "INSERT INTO activity_logs (admin_id, user_id, action, ip_address) 
                          VALUES ($admin_id, $user_id, 'User $action', '$ip')");
}

// Get users list
$users = mysqli_query($mysqli, "SELECT u.*, 
                              (SELECT COUNT(*) FROM book_listings WHERE user_id = u.id) as books_count,
                              (SELECT COUNT(*) FROM digital_files WHERE user_id = u.id) as files_count
                              FROM users u ORDER BY u.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Management - Admin Panel</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
        <!-- Include admin.css for sidebar styling -->
    </head>

    <body>
        <div class="container-fluid">
            <div class="row">
                <!-- Include sidebar -->
                <?php include '../includes/sidebar.php'; ?>

                <!-- Main Content -->
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
                                            <th>Joined</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = mysqli_fetch_assoc($users)): ?>
                                            <tr>
                                                <td><?php echo $user['id']; ?></td>
                                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo htmlspecialchars($user['location']); ?></td>
                                                <td><?php echo $user['books_count']; ?></td>
                                                <td><?php echo $user['files_count']; ?></td>
                                                <td>
                                                    <span
                                                        class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($user['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick="viewUser(<?php echo $user['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($user['status'] === 'active'): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                                onclick="blockUser(<?php echo $user['id']; ?>)">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                                onclick="unblockUser(<?php echo $user['id']; ?>)">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteUser(<?php echo $user['id']; ?>)">
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

        <!-- User View Modal -->
        <div class="modal fade" id="userModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">User Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="userModalBody">
                        <!-- Content will be loaded dynamically -->
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
                $('#usersTable').DataTable({
                    order: [[7, 'desc']],
                    pageLength: 10
                });
            });

            function viewUser(userId) {
                $.get('ajax/get_user.php', { id: userId }, function (data) {
                    $('#userModalBody').html(data);
                    $('#userModal').modal('show');
                });
            }

            function blockUser(userId) {
                if (confirm('Are you sure you want to block this user?')) {
                    submitUserAction(userId, 'block');
                }
            }

            function unblockUser(userId) {
                if (confirm('Are you sure you want to unblock this user?')) {
                    submitUserAction(userId, 'unblock');
                }
            }

            function deleteUser(userId) {
                if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                    submitUserAction(userId, 'delete');
                }
            }

            function submitUserAction(userId, action) {
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
    </body>

</html>