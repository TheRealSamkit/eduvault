<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report']) && isLoggedIn()) {
    $reported_id = (int) $_POST['reported_id'];
    $reported_content = mysqli_real_escape_string($mysqli, $_POST['reported_content']);
    $reason = mysqli_real_escape_string($mysqli, $_POST['report_reason']);
    $user_id = $_SESSION['user_id'];

    if (!empty($reason) && $reported_id > 0) {
        $stmt = $mysqli->prepare("INSERT INTO reported_content (reporter_id, content_type, content_id, reason, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isis", $user_id, $reported_content, $reported_id, $reason);
        $stmt->execute();
        $stmt->close();
        flash('success', 'Thank you for your report. We will review it shortly.');
        redirect("" . $_SERVER['PHP_SELF'] . '?id=' . $reported_id);
        exit();
    } else {
        flash('error', 'Please provide a valid reason for reporting.');
        redirect("" . $_SERVER['PHP_SELF'] . '?id=' . $reported_id);
        exit();
    }
}
?>