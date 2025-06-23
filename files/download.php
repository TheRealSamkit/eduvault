<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect("../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    redirect("list.php");
    exit();
}

$file_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get file information
$query = "SELECT * FROM digital_files WHERE id = ?";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $file_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$file = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$file || !file_exists($file['file_path'])) {
    flash('error', 'File not found or does not exist.');
    redirect("list.php");
    exit();
}

// Record the download
$download_query = "INSERT INTO downloads (user_id, file_id, downloaded_at) VALUES (?, ?, NOW())";
$download_stmt = mysqli_prepare($mysqli, $download_query);
mysqli_stmt_bind_param($download_stmt, 'ii', $user_id, $file_id);
mysqli_stmt_execute($download_stmt);
mysqli_stmt_close($download_stmt);

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