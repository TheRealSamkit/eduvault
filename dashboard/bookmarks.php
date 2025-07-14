<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT f.* FROM file_bookmarks b JOIN digital_files f ON b.file_id = f.id WHERE b.user_id = ? ORDER BY b.bookmarked_at DESC";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$bookmarks = [];
while ($row = mysqli_fetch_assoc($result)) {
    $bookmarks[] = $row;
}
mysqli_stmt_close($stmt);

$pageTitle = 'My Bookmarks';
$sidebar = true;
require_once '../includes/header.php';
?>

<div class="d-flex align-items-start">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">My Bookmarked Files</h2>
                <div>
                    <button id="toggleViewBtn" class="btn btn-outline-secondary btn-sm"><i class="fas fa-th"></i> Toggle
                        View</button>
                </div>
            </div>
            <div id="bookmarksGrid" class="row g-4">
                <?php if (count($bookmarks) > 0): ?>
                    <?php foreach ($bookmarks as $file): ?>
                        <div class="col-12 col-md-6 col-lg-4 bookmark-card">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0 text-truncate">
                                            <a href="files/view.php?slug=<?php echo urlencode($file['slug']); ?>"
                                                class="text-decoration-none color fs-5">
                                                <i
                                                    class="fas fa-file-<?php echo getFileIcon(strtolower($file['file_type'])); ?> file-icon text-primary"></i>
                                                <?php echo htmlspecialchars($file['title']); ?>
                                            </a>
                                        </h6>
                                        <button class="btn btn-sm btn-outline-warning btn-bookmark-file"
                                            data-file-id="<?php echo $file['id']; ?>" title="Remove Bookmark">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </div>
                                    <p class="card-text text-muted small mb-2 text-truncate" style="max-width: 100%;">
                                        <?php echo htmlspecialchars(substr($file['description'], 0, 120)); ?>
                                        <?php if (strlen($file['description']) > 120): ?>...<?php endif; ?>
                                    </p>
                                    <div class="stats-row d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="badge bg-info stats-badge">
                                                <i
                                                    class="fas fa-download me-1"></i><?php echo number_format($file['download_count']); ?>
                                            </span>
                                            <span class="badge bg-warning stats-badge">
                                                <i
                                                    class="fas fa-star me-1"></i><?php echo number_format($file['average_rating'] ?: 0, 1); ?>
                                            </span>
                                            <span class="badge bg-light text-dark stats-badge">
                                                <i class="fas fa-hdd me-1"></i><?php echo formatFileSize($file['file_size']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 d-flex flex-column flex-md-row gap-2">
                                    <a href="files/view.php?slug=<?php echo urlencode($file['slug']); ?>"
                                        class="btn btn-outline-primary btn-sm flex-fill" title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="files/download.php?slug=<?php echo urlencode($file['slug']); ?>"
                                        class="btn btn-success btn-sm flex-fill" title="Download">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <img src="assets/svg/no_files.svg" alt="No bookmarks found" class="img-fluid"
                            style="max-width:180px;">
                        <h5 class="mt-3">No bookmarks found</h5>
                        <p class="text-muted">You haven't bookmarked any files yet.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div id="bookmarksTable" class="table-responsive d-none">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Downloads</th>
                            <th>Rating</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookmarks as $file): ?>
                            <tr class="bookmark-row">
                                <td>
                                    <a href="files/view.php?slug=<?php echo urlencode($file['slug']); ?>"
                                        class="text-decoration-none color">
                                        <i
                                            class="fas fa-file-<?php echo getFileIcon(strtolower($file['file_type'])); ?> text-primary"></i>
                                        <?php echo htmlspecialchars($file['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo strtoupper($file['file_type']); ?></td>
                                <td><?php echo formatFileSize($file['file_size']); ?></td>
                                <td><?php echo number_format($file['download_count']); ?></td>
                                <td><?php echo number_format($file['average_rating'] ?: 0, 1); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-warning btn-bookmark-file"
                                        data-file-id="<?php echo $file['id']; ?>" title="Remove Bookmark">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <a href="files/view.php?slug=<?php echo urlencode($file['slug']); ?>"
                                        class="btn btn-outline-primary btn-sm" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="files/download.php?slug=<?php echo urlencode($file['slug']); ?>"
                                        class="btn btn-success btn-sm" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>