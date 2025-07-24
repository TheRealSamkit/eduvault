<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$sidebar = true;

// $books_count = getCount($mysqli, 'book_listings', 'book_count', $user_id);
$files_count = getCount($mysqli, 'digital_files', 'file_count', $user_id);
$downloads_count = getCount($mysqli, 'downloads', 'downloads_count', $user_id) ?? 0;
$avg_feedback = round(mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT AVG(rating) as avg FROM file_feedback WHERE file_id IN (SELECT id FROM digital_files WHERE user_id = $user_id)"))['avg'] ?? 0, 1);

// Recent uploads
$activity_query = "
    SELECT 'file' as type, id, title, file_type, upload_date as date FROM digital_files WHERE user_id = $user_id
    ORDER BY date DESC LIMIT 3";
$activity_result = mysqli_query($mysqli, $activity_query);

// Recent reports by user
$reports_query = "
    SELECT r.id, r.reason, r.created_at, r.content_type, r.content_id
    FROM reported_content r
    WHERE r.reporter_id = $user_id
    ORDER BY r.created_at DESC LIMIT 5";
$reports_result = mysqli_query($mysqli, $reports_query);

require_once '../includes/header.php';
require_once '../modals/editProfileModal.php';
?>
<div class="d-flex align-items-start">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <div class="container-fluid">
            <h1 class="my-2">Welcome, <?php echo htmlspecialchars($user['name']); ?>!
            </h1>
            <div class="row g-3 mb-4">
                <?php if ($books_enabled): ?>
                    <div class="col-md-3">
                        <div class="card bg-primary shadow-sm text-center p-2">
                            <div class="card-body">
                                <i class="fas fa-book fa-2x mb-2"></i>
                                <h2><?php echo $books_count; ?></h2>
                                <p class="mb-0">Books</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <i class="fas fa-eye fa-2x mb-2"></i>
                            <h2><?php echo htmlspecialchars($user['tokens']); ?></h2>
                            <p class="mb-0">Remaining Tokens</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <h2><?php echo $files_count; ?></h2>
                            <p class="mb-0">Files</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <i class="fas fa-download fa-2x mb-2"></i>
                            <h2><?php echo $downloads_count; ?></h2>
                            <p class="mb-0">Downloads</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <i class="fas fa-star fa-2x mb-2"></i>
                            <h2><?php echo $avg_feedback > 0 ? $avg_feedback : 'No Feedbacks'; ?></h2>
                            <p class="mb-0">Avg Feedback</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row-md-6 g-3 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                        <div class="list-group list-group-flush small rounded">
                            <?php if (mysqli_num_rows($activity_result) > 0): ?>
                                <?php while ($activity = mysqli_fetch_assoc($activity_result)): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-file-<?php echo getFileIcon($activity['file_type']) ?> me-2"></i>
                                            <?php echo htmlspecialchars($activity['title']); ?>
                                        </div>
                                        <small
                                            class="text-muted"><?php echo date("M j, Y", strtotime($activity['date'])); ?></small>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="list-group-item text-muted">No recent activity.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row-md-6 g-3 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        <div class="row gap-2 ms-sm-2">
                            <?php if ($books_enabled): ?>
                                <a href="../books/add.php" class="btn btn-outline-primary col-md-3"><i
                                        class="fas fa-book me-2"></i>Add
                                    New Book</a>
                            <?php endif; ?>
                            <a href="../files/upload.php" class="btn btn-outline-success col-md-3"><i
                                    class="fas fa-upload me-2"></i>Upload File</a>
                            <?php if ($books_enabled): ?>
                                <a href="my_books.php" class="btn btn-outline-secondary col-md-3"><i
                                        class="fas fa-folder-open me-2"></i>Manage My Books</a>
                            <?php endif; ?>
                            <a href="my_uploads.php" class="btn btn-outline-secondary col-md-3"><i
                                    class="fas fa-folder-open me-2"></i>Manage My Files</a>
                            <a href="../pages/change_password.php" class="btn btn-outline-primary col-md-3"><i
                                    class="fas fa-key me-2"></i>Change Password</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5><i class="fas fa-flag me-2"></i>Recent Reports by You</h5>
                    <?php if (mysqli_num_rows($reports_result) > 0): ?>
                        <ul class="list-group list-group-flush small rounded">
                            <?php while ($report = mysqli_fetch_assoc($reports_result)): ?>
                                <li class="list-group-item">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($report['content_type']); ?>
                                        #<?php echo $report['content_id']; ?></span>
                                    <?php echo htmlspecialchars($report['reason']); ?>
                                    <small
                                        class="text-muted d-block"><?php echo date("M j, Y, H:i", strtotime($report['created_at'])); ?></small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted">No reports submitted yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>