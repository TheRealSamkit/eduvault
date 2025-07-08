<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('Invalid request.');
}
$file_id = (int) $_GET['id'];

// Fetch file info securely, including uploader name
$query = "SELECT f.file_path, f.file_type, f.title, f.status, f.visibility, f.verified, u.name as uploader_name FROM digital_files f JOIN users u ON f.user_id = u.id WHERE f.id = ? LIMIT 1";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $file_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$file = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$allowed_types = ['txt', 'csv', 'md'];
if (!$file || !in_array(strtolower($file['file_type']), $allowed_types) || $file['status'] !== 'active' || $file['visibility'] !== 'public' || $file['verified'] != 1) {
    http_response_code(403);
    die('Access denied or file not found.');
}

$real_path = realpath(__DIR__ . '/../' . ltrim(str_replace('..', '', $file['file_path']), '/'));
if (!$real_path || !file_exists($real_path)) {
    http_response_code(404);
    die('File not found.');
}

$content = file_get_contents($real_path);
require_once '../includes/header.php';
?>
<div class="container py-4">
    <h3 class="mb-4"><i class="fas fa-file-alt text-primary me-2"></i><?php echo htmlspecialchars($file['title']); ?>
        (Preview)</h3>
    <div class="card shadow-sm">
        <div class="card-body bg-dark-body">
            <pre
                style="white-space: pre-wrap; word-break: break-all; background: #f8f9fa; border-radius: 8px; padding: 1rem; max-height: 70vh; overflow: auto; color:#000;"><?php echo htmlspecialchars($content); ?></pre>
        </div>
    </div>
    <div class="mt-3 text-muted small">
        <span>Author: <strong><?php echo htmlspecialchars($file['uploader_name']); ?></strong></span><br>
        <span>&copy; <?php echo date('Y'); ?> by <?php echo htmlspecialchars($file['uploader_name']); ?>. All rights
            reserved.</span>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>