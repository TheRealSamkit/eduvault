<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false]));
}

$user_id = $_SESSION['user_id'];
$latitude = mysqli_real_escape_string($mysqli, $_POST['latitude']);
$longitude = mysqli_real_escape_string($mysqli, $_POST['longitude']);

$query = "UPDATE users SET latitude = $latitude, longitude = $longitude 
          WHERE id = $user_id";

$success = mysqli_query($mysqli, $query);
echo json_encode(['success' => $success]);
?>