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

// Fetch file info securely
$query = "SELECT file_path, file_type, status, visibility, verified FROM digital_files WHERE id = ? LIMIT 1";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $file_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$file = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$file || strtolower($file['file_type']) !== 'pdf' || $file['status'] !== 'active' || $file['visibility'] !== 'public' || $file['verified'] != 1) {
    http_response_code(403);
    die('Access denied or file not found.');
}

$real_path = realpath(__DIR__ . '/../' . ltrim(str_replace('..', '', $file['file_path']), '/'));
if (!$real_path || !file_exists($real_path)) {
    http_response_code(404);
    die('File not found.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($real_path) . '"');
header('Content-Length: ' . filesize($real_path));
header('Cache-Control: private, max-age=10800, must-revalidate');
header('Pragma: public');
readfile($real_path);
exit;