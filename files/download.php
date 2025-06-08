<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$file_id = mysqli_real_escape_string($mysqli, $_GET['id']);
$user_id = $_SESSION['user_id'];

// Get file information
$query = "SELECT * FROM digital_files WHERE id = $file_id";
$result = mysqli_query($mysqli, $query);
$file = mysqli_fetch_assoc($result);

if (!$file || !file_exists($file['file_path'])) {
    $_SESSION['error'] = "File not found.";
    header("Location: list.php");
    exit();
}

// Record the download
$download_query = "INSERT INTO downloads (user_id, file_id, downloaded_at) 
                  VALUES ($user_id, $file_id, NOW())";
mysqli_query($mysqli, $download_query);

// Get file mime type
$mime_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
];

$file_ext = strtolower(pathinfo($file['file_path'], PATHINFO_EXTENSION));
$mime_type = isset($mime_types[$file_ext]) ? $mime_types[$file_ext] : 'application/octet-stream';

// Set headers for download
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . basename($file['file_path']) . '"');
header('Content-Length: ' . filesize($file['file_path']));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file content
readfile($file['file_path']);
$_SESSION['success'] = "File downloaded successfully.";
exit();
?>