<?php
header('Access-Control-Allow-Origin: *');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireLogin();
ini_set('log_errors', 1); // turn on logging
ini_set('error_log', __DIR__ . '/my_php_errors.log'); // custom log file in current folder

error_log('User logged in: ' . (isLoggedIn() ? 'yes' : 'no'));
error_log('Session: ' . print_r($_SESSION, true));
if (!isset($_GET['slug']) || !is_string($_GET['slug'])) {
    flash('error', 'Invalid request.');
    http_response_code(400);
    die('Invalid request.');
}
$slug = trim($_GET['slug']);
if (!empty($slug)) {
    // Fetch file id for token check
    $query = "SELECT id FROM digital_files WHERE slug = ? LIMIT 1";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, 's', $slug);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $file = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $is_range = isset($_SERVER['HTTP_RANGE']);
    if ($file && isLoggedIn() && !$is_range) {
        $user_id = $_SESSION['user_id'];
        if (!checkAndConsumeToken($user_id, $file['id'], $mysqli)) {
            flash('error', 'You do not have enough tokens to preview this file. Upload files to earn more tokens!');
            redirect('../dashboard/dashboard.php');
            exit();
        }
    }
}

// Fetch file info securely
$query = "SELECT file_path, file_type, status, visibility, verified FROM digital_files WHERE slug = ? LIMIT 1";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$file = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$file || strtolower($file['file_type']) !== 'pdf' || $file['status'] !== 'active' || $file['visibility'] !== 'public' || $file['verified'] != 1) {
    flash('error', 'Access denied or file not found.');
    http_response_code(403);
    die('Access denied or file not found.');
}

$real_path = realpath(__DIR__ . '/../' . ltrim(str_replace('..', '', $file['file_path']), '/'));
if (!$real_path || !file_exists($real_path)) {
    flash('error', 'File not found.');
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