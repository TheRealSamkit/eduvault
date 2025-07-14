<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$file_id = isset($_POST['file_id']) ? intval($_POST['file_id']) : 0;
if (!$file_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid file ID']);
    exit;
}

// Check if already bookmarked
$query = "SELECT 1 FROM file_bookmarks WHERE user_id = ? AND file_id = ? LIMIT 1";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'ii', $user_id, $file_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$bookmarked = mysqli_fetch_assoc($result) ? true : false;
mysqli_stmt_close($stmt);

if ($bookmarked) {
    // Remove bookmark
    $delete = "DELETE FROM file_bookmarks WHERE user_id = ? AND file_id = ?";
    $stmt = mysqli_prepare($mysqli, $delete);
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $file_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'bookmarked' => false]);
    exit;
} else {
    // Add bookmark
    $insert = "INSERT INTO file_bookmarks (user_id, file_id, bookmarked_at) VALUES (?, ?, NOW())";
    $stmt = mysqli_prepare($mysqli, $insert);
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $file_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'bookmarked' => true]);
    exit;
}