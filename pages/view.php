<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect("../auth/login.php");
    exit();
}

if (!isset($_GET["id"])) {
    redirect("" . $_SERVER['HTTP_REFERER']);
    exit();
}
$get_user_id = intval($_GET["id"]);
$viewer_type = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $get_user_id;

// User info
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $get_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$viewer_type && $user['profile_visibility'] == 'private') {
    flash('error', 'This profile is private.');
    redirect('/eduvault/dashboard/dashboard.php');
    exit();
}


// Get user stats
$files_count = getCount($mysqli, 'digital_files', 'file_count', $get_user_id);
$total_downloads = getCount($mysqli, 'downloads', 'total_downloads', $get_user_id) ?? 0;

// Get average feedback rating for user's uploaded files
$feedback_query = "SELECT AVG(rating) as avg_feedback FROM file_feedback WHERE file_id IN (SELECT id FROM digital_files WHERE user_id = ?)";
$feedback_stmt = mysqli_prepare($mysqli, $feedback_query);
mysqli_stmt_bind_param($feedback_stmt, 'i', $get_user_id);
mysqli_stmt_execute($feedback_stmt);
$feedback_result = mysqli_stmt_get_result($feedback_stmt);
$avg_feedback = round(mysqli_fetch_assoc($feedback_result)['avg_feedback'] ?? 0, 1);
mysqli_stmt_close($feedback_stmt);

// Get user's tokens (with fallback)
$tokens_query = "SELECT tokens FROM users WHERE id = ?";
$tokens_stmt = mysqli_prepare($mysqli, $tokens_query);
mysqli_stmt_bind_param($tokens_stmt, 'i', $get_user_id);
mysqli_stmt_execute($tokens_stmt);
$tokens_result = mysqli_stmt_get_result($tokens_stmt);
$tokens = mysqli_fetch_assoc($tokens_result)['tokens'] ?? 0;
mysqli_stmt_close($tokens_stmt);

// Get recent files with more details
$recent_files_query = "SELECT df.id,df.slug, df.title, df.file_type, df.upload_date, df.download_count, df.average_rating, 
                              s.name as subject_name, c.name as course_name
                       FROM digital_files df 
                       LEFT JOIN subjects s ON df.subject_id = s.id
                       LEFT JOIN courses c ON df.course_id = c.id
                       WHERE df.user_id = ? AND df.status = 'active' 
                       ORDER BY df.upload_date DESC LIMIT 6";
$recent_files_stmt = mysqli_prepare($mysqli, $recent_files_query);
mysqli_stmt_bind_param($recent_files_stmt, 'i', $get_user_id);
mysqli_stmt_execute($recent_files_stmt);
$recent_files = mysqli_stmt_get_result($recent_files_stmt);

$bookmarked_files = null;
if ($viewer_type) {
    $bookmarks_query = "SELECT df.id, df.title, df.slug,df.file_type, df.upload_date, df.download_count, df.average_rating, s.name as subject_name, c.name as course_name
        FROM file_bookmarks fb
        JOIN digital_files df ON fb.file_id = df.id
        LEFT JOIN subjects s ON df.subject_id = s.id
        LEFT JOIN courses c ON df.course_id = c.id
        WHERE fb.user_id = ? AND df.status = 'active'
        ORDER BY fb.bookmarked_at DESC LIMIT 6";
    $bookmarks_stmt = mysqli_prepare($mysqli, $bookmarks_query);
    mysqli_stmt_bind_param($bookmarks_stmt, 'i', $get_user_id);
    mysqli_stmt_execute($bookmarks_stmt);
    $bookmarked_files = mysqli_stmt_get_result($bookmarks_stmt);
}
$sidebar = true;

include '../includes/header.php';
echo '<link rel="stylesheet" href="../assets/css/profile.css">';
require_once '../modals/reportmodal.php';
require_once '../modals/avatarModal.php';
$test = intval($_SESSION['user_id']) == isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($test) {
    require_once '../modals/editProfileModal.php';
}
?>

<div class="d-flex align-items-start">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <div class="container-md">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="profile-header">
                    <img src="<?php echo htmlspecialchars($user['avatar_path']); ?>" class="profile-avatar"
                        alt="<?php echo htmlspecialchars($user['name']); ?>" data-bs-toggle="modal"
                        data-bs-target="#avatarModal">

                    <div class="profile-info">
                        <h1 class="profile-username"><?php echo htmlspecialchars($user['name']); ?></h1>

                        <div class="profile-stats">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $files_count; ?></div>
                                <div class="stat-label">Files</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $total_downloads; ?></div>
                                <div class="stat-label">Downloads</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $tokens; ?></div>
                                <div class="stat-label">Tokens</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $avg_feedback > 0 ? $avg_feedback . '★' : 'N/A'; ?>
                                </div>
                                <div class="stat-label">Rating</div>
                            </div>
                        </div>

                        <div class="profile-bio">
                            <?php if ($viewer_type): ?>
                                <div><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?></div>
                            <?php endif; ?>
                            <?php if ($user['location']): ?>
                                <div><i
                                        class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($user['location']); ?>
                                </div>
                            <?php endif; ?>
                            <div><i class="fas fa-calendar me-2"></i>Member since
                                <?php echo date("F Y", strtotime($user['created_at'])); ?>
                            </div>
                            <?php if ($user['last_active']): ?>
                                <div><i class="fas fa-clock me-2"></i>Last active
                                    <?php echo date("M j, Y", strtotime($user['last_active'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="profile-actions">
                            <?php if (!$viewer_type): ?>
                                <button class="btn-profile btn-danger-profile" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal" data-content-type="user"
                                    data-report-id="<?php echo $user['id']; ?>"
                                    data-report-title="<?php echo htmlspecialchars($user['name']); ?>">
                                    <i class="fas fa-flag"></i> Report
                                </button>
                            <?php else: ?>
                                <button class="btn-profile btn-primary-profile" data-bs-toggle="modal"
                                    data-bs-target="#editProfileModal">
                                    <i class="fas fa-user-edit"></i> Edit Profile
                                </button>
                                <a href="/eduvault/pages/change_password.php" class="btn-profile">
                                    <i class="fas fa-key"></i> Change Password
                                </a>
                                <a href="/eduvault/files/upload.php" class="btn-profile btn-primary-profile">
                                    <i class="fas fa-upload"></i> Upload File
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="profile-tabs">
                    <div class="tab-nav">
                        <a href="#files" class="tab-link active" data-tab="files">
                            <i class="fas fa-file-alt me-1"></i> Files
                        </a>
                        <?php if ($viewer_type): ?>
                            <a href="#bookmarks" class="tab-link" data-tab="bookmarks">
                                <i class="fas fa-bookmark me-1"></i> Bookmarks
                            </a>
                        <?php endif; ?>
                    </div>

                    <div id="files" class="tab-content">
                        <?php if (mysqli_num_rows($recent_files) > 0): ?>
                            <div class="files-grid">
                                <?php while ($file = mysqli_fetch_assoc($recent_files)): ?>
                                    <div class="file-card">
                                        <div class="file-preview">
                                            <i class="fas fa-file-<?php echo getFileIcon($file['file_type']); ?> file-icon"></i>
                                        </div>
                                        <div class="file-info">
                                            <div class="file-title">
                                                <a href="../files/view.php?slug=<?php echo $file['slug']; ?>"
                                                    class="text-decoration-none text-body">
                                                    <?php echo htmlspecialchars($file['title']); ?>
                                                </a>
                                            </div>
                                            <div class="file-meta">
                                                <?php if ($file['subject_name']): ?>
                                                    <i
                                                        class="fas fa-book me-1"></i><?php echo htmlspecialchars($file['subject_name']); ?>
                                                <?php endif; ?>
                                                <?php if ($file['course_name']): ?>
                                                    <span class="ms-2"><i
                                                            class="fas fa-graduation-cap me-1"></i><?php echo htmlspecialchars($file['course_name']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="file-stats">
                                                <div class="stat-icon">
                                                    <i class="fas fa-download"></i>
                                                    <span><?php echo $file['download_count'] ?? 0; ?></span>
                                                </div>
                                                <div class="stat-icon">
                                                    <i class="fas fa-star"></i>
                                                    <span><?php echo $file['average_rating'] ? round($file['average_rating'], 1) : 'N/A'; ?></span>
                                                </div>
                                                <div class="stat-icon">
                                                    <i class="fas fa-calendar"></i>
                                                    <span><?php echo date("M j", strtotime($file['upload_date'])); ?></span>
                                                </div>
                                            </div>
                                            <?php if (isLoggedIn()): ?>
                                                <div class="d-flex gap-2 mt-2">
                                                    <a href="../files/download.php?slug=<?php echo urlencode($file['slug']); ?>"
                                                        class="btn btn-success btn-sm flex-fill">
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </a>
                                                    <?php if (strtolower($file['file_type']) === 'pdf'): ?>
                                                        <a href="/eduvault/pdfjs/web/viewer.php?slug=<?php echo urlencode($file['slug']); ?>"
                                                            target="_blank" class="btn btn-outline-secondary btn-sm flex-fill"
                                                            title="Full Page PDF Preview">
                                                            <i class="fas fa-eye me-1"></i>Preview
                                                        </a>
                                                    <?php elseif (in_array(strtolower($file['file_type']), ['txt', 'csv', 'md'])): ?>
                                                        <a href="../files/txt_preview.php?slug=<?php echo urlencode($file['slug']); ?>"
                                                            target="_blank" class="btn btn-outline-secondary btn-sm flex-fill"
                                                            title="Full Page Text Preview">
                                                            <i class="fas fa-eye me-1"></i>Preview
                                                        </a>
                                                    <?php elseif (in_array(strtolower($file['file_type']), ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm flex-fill btn-preview-file"
                                                            data-file-slug="<?php echo urlencode($file['slug']); ?>"
                                                            data-file-type="<?php echo strtolower($file['file_type']); ?>"
                                                            data-file-title="<?php echo htmlspecialchars($file['title']); ?>"
                                                            data-preview-type="image">
                                                            <i class="fas fa-eye me-1"></i>Preview
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm flex-fill btn-preview-file"
                                                            data-file-slug="<?php echo urlencode($file['slug']); ?>"
                                                            data-file-type="<?php echo strtolower($file['file_type']); ?>"
                                                            data-file-title="<?php echo htmlspecialchars($file['title']); ?>">
                                                            <i class="fas fa-eye me-1"></i>Preview
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-file-upload"></i>
                                <h4>No files yet</h4>
                                <?php if ($viewer_type): ?>
                                    <p>Start sharing your knowledge by uploading your first document!</p>
                                    <a href="/eduvault/files/upload.php" class="btn-profile btn-primary-profile">
                                        <i class="fas fa-upload"></i> Upload Your First File
                                    </a>
                                <?php else: ?>
                                    <p>This user hasn't uploaded any files yet.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($viewer_type): ?>
                        <div id="bookmarks" class="tab-content" style="display: none;">
                            <?php if ($bookmarked_files && mysqli_num_rows($bookmarked_files) > 0): ?>
                                <div class="files-grid">
                                    <?php while ($file_b = mysqli_fetch_assoc($bookmarked_files)): ?>
                                        <div class="file-card">
                                            <div class="file-preview">
                                                <i class="fas fa-file-<?php echo getFileIcon($file_b['file_type']); ?> file-icon"></i>
                                            </div>
                                            <div class="file-info">
                                                <div class="file-title">
                                                    <a href="../files/view.php?slug=<?php echo $file_b['slug']; ?>"
                                                        class="text-decoration-none text-body">
                                                        <?php echo htmlspecialchars($file_b['title']); ?>
                                                    </a>
                                                </div>
                                                <div class="file-meta">
                                                    <?php if ($file_b['subject_name']): ?>
                                                        <i
                                                            class="fas fa-book me-1"></i><?php echo htmlspecialchars($file_b['subject_name']); ?>
                                                    <?php endif; ?>
                                                    <?php if ($file_b['course_name']): ?>
                                                        <span class="ms-2"><i
                                                                class="fas fa-graduation-cap me-1"></i><?php echo htmlspecialchars($file_b['course_name']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="file-stats">
                                                    <div class="stat-icon">
                                                        <i class="fas fa-download"></i>
                                                        <span><?php echo $file_b['download_count'] ?? 0; ?></span>
                                                    </div>
                                                    <div class="stat-icon">
                                                        <i class="fas fa-star"></i>
                                                        <span><?php echo $file_b['average_rating'] ? round($file_b['average_rating'], 1) : 'N/A'; ?></span>
                                                    </div>
                                                    <div class="stat-icon">
                                                        <i class="fas fa-calendar"></i>
                                                        <span><?php echo date("M j", strtotime($file_b['upload_date'])); ?></span>
                                                    </div>
                                                </div>
                                                <?php if (isLoggedIn()): ?>
                                                    <div class="d-flex gap-2 mt-2">
                                                        <a href="../files/download.php?slug=<?php echo urlencode($file_b['slug']); ?>"
                                                            class="btn btn-success btn-sm flex-fill">
                                                            <i class="fas fa-download me-1"></i>Download
                                                        </a>
                                                        <?php if (strtolower($file_b['file_type']) === 'pdf'): ?>
                                                            <a href="/eduvault/pdfjs/web/viewer.php?slug=<?php echo urlencode($file_b['slug']); ?>"
                                                                target="_blank" class="btn btn-outline-secondary btn-sm flex-fill"
                                                                title="Full Page PDF Preview">
                                                                <i class="fas fa-eye me-1"></i>Preview
                                                            </a>
                                                        <?php elseif (in_array(strtolower($file_b['file_type']), ['txt', 'csv', 'md'])): ?>
                                                            <a href="../files/txt_preview.php?slug=<?php echo urlencode($file_b['slug']); ?>"
                                                                target="_blank" class="btn btn-outline-secondary btn-sm flex-fill"
                                                                title="Full Page Text Preview">
                                                                <i class="fas fa-eye me-1"></i>Preview
                                                            </a>
                                                        <?php elseif (in_array(strtolower($file_b['file_type']), ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                                            <button type="button"
                                                                class="btn btn-outline-secondary btn-sm flex-fill btn-preview-file"
                                                                data-file-slug="<?php echo urlencode($file_b['slug']); ?>"
                                                                data-file-type="<?php echo strtolower($file_b['file_type']); ?>"
                                                                data-file-title="<?php echo htmlspecialchars($file_b['title']); ?>"
                                                                data-preview-type="image">
                                                                <i class="fas fa-eye me-1"></i>Preview
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button"
                                                                class="btn btn-outline-secondary btn-sm flex-fill btn-preview-file"
                                                                data-file-slug="<?php echo urlencode($file_b['slug']); ?>"
                                                                data-file-type="<?php echo strtolower($file_b['file_type']); ?>"
                                                                data-file-title="<?php echo htmlspecialchars($file_b['title']); ?>">
                                                                <i class="fas fa-eye me-1"></i>Preview
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-bookmark"></i>
                                    <h4>No bookmarks yet</h4>
                                    <p>Bookmark files to see them here.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <h4>User not found</h4>
                    <p>The user you're looking for doesn't exist or has been removed.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
require_once '../includes/footer.php'; ?>