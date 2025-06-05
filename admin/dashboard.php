<?php
require_once '../includes/db_connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get statistics

// Get recent activities
$activities = mysqli_query($mysqli, "SELECT al.*, u.name as user_name, au.username as admin_name 
                                   FROM activity_logs al 
                                   LEFT JOIN users u ON al.user_id = u.id 
                                   LEFT JOIN admin_users au ON al.admin_id = au.id 
                                   ORDER BY al.created_at DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard - EduVault</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <style>
            .stat-card {
                border-radius: 10px;
                border: none;
                transition: transform 0.3s;
            }

            .stat-card:hover {
                transform: translateY(-5px);
            }
        </style>
    </head>

    <body>
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <?php include '../includes/sidebar.php'; ?>


                <!-- Main Content -->
                <div class="col-md-10 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Dashboard Overview</h2>
                        <span class="text-muted">Welcome, <?php echo $_SESSION['admin_role']; ?></span>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card bg-primary text-white">
                                <div class="card-body">
                                    <h3 class="card-title"><?php echo $stats['users']; ?></h3>
                                    <p class="card-text">Total Users</p>
                                    <i
                                        class="fas fa-users fa-2x position-absolute end-0 bottom-0 mb-3 me-3 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body">
                                    <h3 class="card-title"><?php echo $stats['books']; ?></h3>
                                    <p class="card-text">Books Listed</p>
                                    <i
                                        class="fas fa-book fa-2x position-absolute end-0 bottom-0 mb-3 me-3 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-info text-white">
                                <div class="card-body">
                                    <h3 class="card-title"><?php echo $stats['files']; ?></h3>
                                    <p class="card-text">Study Materials</p>
                                    <i
                                        class="fas fa-file-alt fa-2x position-absolute end-0 bottom-0 mb-3 me-3 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-warning text-white">
                                <div class="card-body">
                                    <h3 class="card-title"><?php echo $stats['reports']; ?></h3>
                                    <p class="card-text">Pending Reports</p>
                                    <i
                                        class="fas fa-flag fa-2x position-absolute end-0 bottom-0 mb-3 me-3 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activities</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="activitiesTable">
                                    <thead>
                                        <tr>
                                            <th>Action</th>
                                            <th>User</th>
                                            <th>Admin</th>
                                            <th>IP Address</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($activity = mysqli_fetch_assoc($activities)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                                <td><?php echo htmlspecialchars($activity['user_name'] ?? 'System'); ?></td>
                                                <td><?php echo htmlspecialchars($activity['admin_name'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($activity['ip_address']); ?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
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
                $('#activitiesTable').DataTable({
                    order: [[4, 'desc']],
                    pageLength: 5,
                    lengthMenu: [5, 10, 25, 50]
                });
            });
        </script>
    </body>

</html>