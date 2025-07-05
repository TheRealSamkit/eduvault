<?php
session_start();
require_once '../includes/db_connect.php';

// Step 1: Get token and verify with Google
$token = $_POST['credential'] ?? '';
$client_id = '982609216899-e94n99lb6b4mi9n1gdbs395at8lrt6hc.apps.googleusercontent.com';

$verify_url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $token;
$response = file_get_contents($verify_url);
$user_info = json_decode($response, true);

// Step 2: Validate response
if (!isset($user_info['email']) || $user_info['aud'] !== $client_id) {
    die('Invalid Token');
}

// Step 3: Extract info
$name = $user_info['name'];
$email = $user_info['email'];
$picture = $user_info['picture'] ?? 'uploads/avatars/default.png';

// Step 4: Check if user exists
$stmt = mysqli_prepare($mysqli, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    // ✅ Existing user → Login
    mysqli_stmt_bind_result($stmt, $user_id);
    mysqli_stmt_fetch($stmt);

    $_SESSION['user_id'] = $user_id;
} else {
    // 🆕 New user → Register
    $password = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT); // random strong password
    $insert_stmt = mysqli_prepare($mysqli, "INSERT INTO users (name, email, avatar_path, password) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($insert_stmt, 'ssss', $name, $email, $picture, $password);
    mysqli_stmt_execute($insert_stmt);
    $_SESSION['user_id'] = mysqli_insert_id($mysqli);
}

header("Location: ../dashboard/dashboard.php");
exit;
