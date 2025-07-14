<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => 'Invalid request'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'mark_read':
            if (isset($_POST['notification_id'])) {
                $notification_id = (int) $_POST['notification_id'];
                if (markNotificationAsRead($notification_id, $user_id, $mysqli)) {
                    $response = ['success' => true, 'message' => 'Notification marked as read'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to mark notification as read'];
                }
            }
            break;

        case 'mark_all_read':
            if (markAllNotificationsAsRead($user_id, $mysqli)) {
                $response = ['success' => true, 'message' => 'All notifications marked as read'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to mark notifications as read'];
            }
            break;

        case 'get_unread_count':
            $count = getUnreadNotificationCount($user_id, $mysqli);
            $response = ['success' => true, 'count' => $count];
            break;

        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>