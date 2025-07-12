<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    flash('error', 'You must be logged in to download files.');
    redirect("../auth/login.php");
    exit();
}

if (!isset($_GET['slug'])) {
    flash('error', 'No file specified.');
    redirect("list.php");
    exit();
}

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$user_id = $_SESSION['user_id'];

// Get file information
$query = "SELECT * FROM digital_files WHERE slug = ?";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$file = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$file || !file_exists($file['file_path'])) {
    flash('error', 'File not found or does not exist.');
    redirect("list.php");
    exit();
}

if (!checkAndConsumeToken($user_id, $file['id'], $mysqli)) {
    flash('error', 'You do not have enough tokens to download this file. Upload files to earn more tokens !');
    redirect($_SERVER['HTTP_REFERER'] ?? '../dashboard/dashboard.php');
    exit();
}

// Unique download count logic (24-hour interval)
$file_id = $file['id']; // Ensure $file is fetched above
$interval_hours = 24;

// Check if user downloaded this file within the last 24 hours
$query = "SELECT downloaded_at FROM downloads WHERE user_id = ? AND file_id = ? ORDER BY downloaded_at DESC LIMIT 1";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'ii', $user_id, $file_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$last_download = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$should_increment = true;
if ($last_download) {
    $last_time = strtotime($last_download['downloaded_at']);
    if (time() - $last_time < $interval_hours * 3600) {
        $should_increment = false;
    }
}

if ($should_increment) {
    // Increment download count in digital_files
    $update = "UPDATE digital_files SET download_count = download_count + 1 WHERE id = ?";
    $stmt = mysqli_prepare($mysqli, $update);
    mysqli_stmt_bind_param($stmt, 'i', $file_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Log this download
    $now = date('Y-m-d H:i:s');
    $insert = "INSERT INTO file_downloads (user_id, file_id, downloaded_at) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($mysqli, $insert);
    mysqli_stmt_bind_param($stmt, 'iis', $user_id, $file_id, $now);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Get file mime type from mimes table
$ext = strtolower(pathinfo($file['file_path'], PATHINFO_EXTENSION));
$mime_type = 'application/octet-stream';
$stmt = mysqli_prepare($mysqli, "SELECT mime_types FROM mimes WHERE extension = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $ext);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $db_mime);
if (mysqli_stmt_fetch($stmt) && $db_mime) {
    $mime_type = $db_mime;
}
mysqli_stmt_close($stmt);

// Set headers for download
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . basename($file['file_path']) . '"');
header('Content-Length: ' . filesize($file['file_path']));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file content
readfile($file['file_path']);
flash('success', 'File downloaded successfully.');
exit();
?>