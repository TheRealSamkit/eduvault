<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) {
    flash('error', 'Not authorized');
    redirect('/eduvault/auth/login.php');
    exit();
}

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (empty($slug)) {
    http_response_code(400);
    flash('error', 'Invalid request');
    redirect($_SERVER['HTTP_REFERER'] ?? '/eduvault/files/list.php');
    exit('Invalid request');
}

$query = "SELECT id, file_path, file_type FROM digital_files WHERE slug = ? LIMIT 1";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$file = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$file) {
    flash('error', 'File not found');
    redirect('/eduvault/files/list.php');
    exit('File not found');
}

$user_id = $_SESSION['user_id'];
if (!checkAndConsumeToken($user_id, $file['id'], $mysqli)) {

    flash('error', 'Not enough tokens');
    redirect('/eduvault/files/list.php');
    exit('Not enough tokens');
}

$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array(strtolower($file['file_type']), $allowed_types)) {
    flash('error', 'Not an image');
    redirect('/eduvault/files/list.php');
    exit('Not an image');
}

// Remove leading ../ if present
$relative_path = preg_replace('/^(\.\.\/)+/', '', $file['file_path']);
$real_path = realpath(__DIR__ . '/../' . $relative_path);
if (!$real_path || !file_exists($real_path)) {
    flash('error', 'File not found');
    redirect('/eduvault/files/list.php');
    exit('File not found');
}

$mime_type = mime_content_type($real_path);
header('Content-Type: ' . $mime_type);
header('Content-Length: ' . filesize($real_path));
readfile($real_path);
exit;