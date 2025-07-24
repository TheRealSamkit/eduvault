<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$sidebar = true;

// Handle mark as read action
if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    $notification_id = (int) $_POST['notification_id'];
    $update_query = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($mysqli, $update_query);
    mysqli_stmt_bind_param($stmt, "ii", $notification_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Redirect to prevent form resubmission
    header("Location: notifications.php");
    exit();
}

// Handle delete old notifications action (delete all read notifications older than 30 days)
if (isset($_POST['delete_old_notifications'])) {
    $delete_query = "DELETE FROM notifications WHERE user_id = ? AND is_read = 1 AND created_at < (NOW() - INTERVAL 30 DAY)";
    $stmt = mysqli_prepare($mysqli, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    // Redirect to prevent form resubmission
    header("Location: notifications.php");
    exit();
}

// Handle mark all as read action
if (isset($_POST['mark_all_read'])) {
    $update_query = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0";
    $stmt = mysqli_prepare($mysqli, $update_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Redirect to prevent form resubmission
    header("Location: notifications.php");
    exit();
}

// Get notifications with pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$count_query = "SELECT COUNT(*) as total FROM notifications WHERE user_id = ?";
$stmt = mysqli_prepare($mysqli, $count_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$total_notifications = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_notifications / $per_page);

// Get notifications
$notifications_query = "
    SELECT n.*, df.title as file_title, df.slug as file_slug, u.name as related_user_name
    FROM notifications n
    LEFT JOIN digital_files df ON n.related_file_id = df.id
    LEFT JOIN users u ON n.related_user_id = u.id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = mysqli_prepare($mysqli, $notifications_query);
mysqli_stmt_bind_param($stmt, "iii", $user_id, $per_page, $offset);
mysqli_stmt_execute($stmt);
$notifications_result = mysqli_stmt_get_result($stmt);

// Get unread count for badge
$unread_query = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = mysqli_prepare($mysqli, $unread_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$unread_result = mysqli_stmt_get_result($stmt);
$unread_count = mysqli_fetch_assoc($unread_result)['unread'];

require_once '../includes/header.php';
?>

<div class="d-flex align-items-start">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0 page-title">
                    <i class="fas fa-bell me-2"></i>Notifications
                    <?php if ($unread_count > 0): ?>
                        <span class="badge bg-danger ms-2"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </h1>
                <div class="d-flex gap-2">
                    <?php if ($unread_count > 0): ?>
                        <button type="button" class="btn btn-outline-primary btn-sm mark-all-read-btn">
                            <i class="fas fa-check-double me-1"></i>Mark All as Read
                        </button>
                    <?php endif; ?>
                    <?php if ($total_notifications > 0): ?>
                        <form method="POST" class="d-inline" id="deleteOldNotificationsForm">
                            <button type="submit" name="delete_old_notifications" class="btn btn-outline-danger btn-sm"
                                id="deleteOldNotificationsBtn">
                                <i class="fas fa-trash me-1"></i>Delete Old Notifications
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (mysqli_num_rows($notifications_result) > 0): ?>
                <div class="card shadow-sm border-0 p-1 mb-4">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php while ($notification = mysqli_fetch_assoc($notifications_result)): ?>
                                <div
                                    class="list-group-item <?php echo $notification['is_read'] ? '' : 'bg-light'; ?> border-0 py-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <?php
                                                $icon_class = 'fas fa-info-circle';
                                                $icon_color = 'text-info';
                                                switch ($notification['type']) {
                                                    case 'download':
                                                        $icon_class = 'fas fa-download';
                                                        $icon_color = 'text-success';
                                                        break;
                                                    case 'feedback':
                                                        $icon_class = 'fas fa-star';
                                                        $icon_color = 'text-warning';
                                                        break;
                                                    case 'system':
                                                        $icon_class = 'fas fa-cog';
                                                        $icon_color = 'text-primary';
                                                        break;
                                                    case 'token':
                                                        $icon_class = 'fas fa-coins';
                                                        $icon_color = 'text-warning';
                                                        break;
                                                    case 'file_approved':
                                                        $icon_class = 'fas fa-check-circle';
                                                        $icon_color = 'text-success';
                                                        break;
                                                    case 'file_rejected':
                                                        $icon_class = 'fas fa-times-circle';
                                                        $icon_color = 'text-danger';
                                                        break;
                                                    case 'bookmark':
                                                        $icon_class = 'fas fa-bookmark';
                                                        $icon_color = 'text-primary';
                                                        break;
                                                    case 'report_resolved':
                                                        $icon_class = 'fas fa-flag-checkered';
                                                        $icon_color = 'text-info';
                                                        break;
                                                }
                                                ?>
                                                <i class="<?php echo $icon_class; ?> <?php echo $icon_color; ?> me-3"></i>
                                                <h6
                                                    class="mb-0 notification-title <?php echo $notification['is_read'] ? 'text-muted' : 'fw-bold'; ?>">
                                                    <?php echo htmlspecialchars($notification['title']); ?>
                                                </h6>
                                                <?php if (!$notification['is_read']): ?>
                                                    <span class="badge bg-primary ms-2">New</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-muted mb-2"><?php echo htmlspecialchars($notification['message']); ?>
                                            </p>

                                            <?php if ($notification['file_title']): ?>
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-file me-1"></i>
                                                        <a href="../files/view.php?slug=<?php echo htmlspecialchars($notification['file_slug']); ?>"
                                                            class="text-decoration-none">
                                                            <?php echo htmlspecialchars($notification['file_title']); ?>
                                                        </a>
                                                    </small>
                                                </div>
                                            <?php endif; ?>

                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date("M j, Y g:i A", strtotime($notification['created_at'])); ?>
                                                <?php if ($notification['read_at']): ?>
                                                    <span class="ms-2">
                                                        <i class="fas fa-check me-1"></i>Read
                                                        <?php echo date("M j, Y g:i A", strtotime($notification['read_at'])); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </small>
                                        </div>

                                        <?php if (!$notification['is_read']): ?>
                                            <button type="button" class="btn btn-outline-success btn-sm mark-read-btn ms-3"
                                                data-notification-id="<?php echo $notification['id']; ?>">
                                                <i class="fas fa-check me-1"></i>Mark as Read
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Notifications pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No notifications yet</h4>
                        <p class="text-muted">You'll see notifications here when you receive feedback, downloads, or system
                            updates.</p>
                        <a href="../index.php" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Go to Homepage
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>