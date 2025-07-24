<?php
require_once '../includes/db_connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    redirect('index.php');
}

// Get stats using new schema
$s = [
    'users' => 0,
    'files' => 0,
    'reports' => 0,
    'downloads' => 0,
    'feedback' => 0,
    'tokens' => 0
];

// Total users
$res = mysqli_query($mysqli, "SELECT COUNT(*) as cnt FROM users");
if ($row = mysqli_fetch_assoc($res))
    $s['users'] = $row['cnt'];
// Total files
$res = mysqli_query($mysqli, "SELECT COUNT(*) as cnt FROM digital_files WHERE status = 'active'");
if ($row = mysqli_fetch_assoc($res))
    $s['files'] = $row['cnt'];
// Pending reports
$res = mysqli_query($mysqli, "SELECT COUNT(*) as cnt FROM reported_content WHERE status = 'pending'");
if ($row = mysqli_fetch_assoc($res))
    $s['reports'] = $row['cnt'];
// Total downloads
$res = mysqli_query($mysqli, "SELECT COUNT(*) as count FROM downloads");
if ($row = mysqli_fetch_assoc($res))
    $s['downloads'] = $row['count'];
// Total feedback
$res = mysqli_query($mysqli, "SELECT COUNT(*) as cnt FROM file_feedback");
if ($row = mysqli_fetch_assoc($res))
    $s['feedback'] = $row['cnt'];
// Total tokens awarded
$res = mysqli_query($mysqli, "SELECT SUM(tokens) as total_tokens FROM users");
if ($row = mysqli_fetch_assoc($res))
    $s['tokens'] = $row['total_tokens'] ?? 0;

// Get recent activities
$activities = mysqli_query($mysqli, "SELECT al.*, u.name as user_name, au.username as admin_name FROM activity_logs al LEFT JOIN users u ON al.id = u.id LEFT JOIN admin_users au ON al.admin_id = au.id ORDER BY al.created_at DESC LIMIT 10");
$title = 'Admin Dashboard - EduVault';
require_once '../includes/admin_header.php';
?>
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
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Dashboard Overview</h2>
                <span class="text-muted">Welcome, <?php echo $_SESSION['admin_role']; ?></span>
            </div>
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo $s['users']; ?></h3>
                            <p class="card-text">Total Users</p>
                            <i class="fas fa-users fa-2x position-absolute end-0 bottom-0 mb-3 me-3 opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo $s['files']; ?></h3>
                            <p class="card-text">Study Materials</p>
                            <i class="fas fa-file-alt fa-2x position-absolute end-0 bottom-0 mb-3 me-3 opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo $s['reports']; ?></h3>
                            <p class="card-text">Pending Reports</p>
                            <i class="fas fa-flag fa-2x position-absolute end-0 bottom-0 mb-3 me-3 opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body">
                            <h3 class="card-title"><?php
                            echo $s['downloads']; ?></h3>
                            <p class="card-text">Total Downloads</p>
                            <i class="fas fa-download fa-2x position-absolute end-0 bottom-0 mb-3 me-3 opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card stat-card bg-secondary text-white">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo $s['feedback']; ?></h3>
                            <p class="card-text">Feedback Entries</p>
                            <i class="fas fa-star fa-2x position-absolute end-0 bottom-0 mb-3 me-3 opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card stat-card bg-dark text-white">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo $s['tokens']; ?></h3>
                            <p class="card-text">Total Tokens</p>
                            <i class="fas fa-coins fa-2x position-absolute end-0 bottom-0 mb-3 me-3 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header ">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activities</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-white" id="activitiesTable">
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
                                        <td><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></td>
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
        $('#activitiesTable').DataTable({
            order: [[4, 'desc']],
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50]
        });
    });
</script>