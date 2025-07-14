<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
$sidebar = true;
if (!isLoggedIn()) {
    redirect("../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];



// Fetch user settings
$query = "SELECT name, email FROM users WHERE id = ?";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get user preferences
$preferences = getAllUserPreferences($user_id, $mysqli);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = true;

    // Update preferences
    $preference_updates = [
        'theme' => in_array($_POST['theme_preference'], ['light', 'dark', 'auto']) ? $_POST['theme_preference'] : 'auto',
        'privacy_level' => in_array($_POST['profile_visibility'], ['public', 'private']) ? $_POST['profile_visibility'] : 'public',
        'email_notifications' => isset($_POST['notification_email']) ? '1' : '0',
        'notify_downloads' => isset($_POST['notify_downloads']) ? '1' : '0',
        'notify_feedback' => isset($_POST['notify_feedback']) ? '1' : '0',
        'notify_tokens' => isset($_POST['notify_tokens']) ? '1' : '0',
        'newsletter' => isset($_POST['newsletter']) ? '1' : '0',
        'allow_feedback' => isset($_POST['allow_feedback']) ? '1' : '0',
        'search_history' => isset($_POST['search_history']) ? '1' : '0',
        'activity_visibility' => in_array($_POST['activity_visibility'], ['public', 'private']) ? $_POST['activity_visibility'] : 'public'
    ];

    // Update download threshold if provided
    if (isset($_POST['notify_downloads_threshold']) && is_numeric($_POST['notify_downloads_threshold'])) {
        $preference_updates['notify_downloads_threshold'] = $_POST['notify_downloads_threshold'];
    }

    foreach ($preference_updates as $key => $value) {
        if (!setUserPreference($user_id, $key, $value, $mysqli)) {
            $success = false;
        }
    }

    if ($success) {
        $_SESSION['toasts'][] = ['type' => 'success', 'message' => 'Settings updated successfully.'];
        // Refresh preferences
        $preferences = getAllUserPreferences($user_id, $mysqli);
    } else {
        $_SESSION['toasts'][] = ['type' => 'error', 'message' => 'Failed to update some settings.'];
    }
}

$pageTitle = 'Settings';
include '../includes/header.php';
?>
<div class="d-flex align-items-start">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <div class="container-md">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Settings</h4>
                </div>
                <div class="card-body">
                    <form method="POST" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label">Theme Preference</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="theme_preference" id="themeLight"
                                        value="light" <?php if (($preferences['theme'] ?? 'auto') === 'light')
                                            echo 'checked'; ?>>
                                    <label class="form-check-label" for="themeLight"><i
                                            class="fas fa-sun me-1"></i>Light</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="theme_preference" id="themeDark"
                                        value="dark" <?php if (($preferences['theme'] ?? 'auto') === 'dark')
                                            echo 'checked'; ?>>
                                    <label class="form-check-label" for="themeDark"><i
                                            class="fas fa-moon me-1"></i>Dark</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="theme_preference"
                                        id="themeSystem" value="auto" <?php if (($preferences['theme'] ?? 'auto') === 'auto')
                                            echo 'checked'; ?>>
                                    <label class="form-check-label" for="themeSystem"><i
                                            class="fas fa-desktop me-1"></i>Auto</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Profile Visibility</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="profile_visibility"
                                        id="profilePublic" value="public" <?php if (($preferences['privacy_level'] ?? 'public') === 'public')
                                            echo 'checked'; ?>>
                                    <label class="form-check-label" for="profilePublic"><i
                                            class="fas fa-globe me-1"></i>Public</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="profile_visibility"
                                        id="profilePrivate" value="private" <?php if (($preferences['privacy_level'] ?? 'public') === 'private')
                                            echo 'checked'; ?>>
                                    <label class="form-check-label" for="profilePrivate"><i
                                            class="fas fa-lock me-1"></i>Private</label>
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3"><i class="fas fa-bell me-2"></i>Notification Settings</h5>

                        <div class="mb-3">
                            <label class="form-label">Email Notifications</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notification_email"
                                    name="notification_email" value="1" <?php if (($preferences['email_notifications'] ?? '1') === '1')
                                        echo 'checked'; ?>>
                                <label class="form-check-label" for="notification_email">Receive important updates via
                                    email</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Download Notifications</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_downloads"
                                    name="notify_downloads" value="1" <?php if (($preferences['notify_downloads'] ?? '1') === '1')
                                        echo 'checked'; ?>>
                                <label class="form-check-label" for="notify_downloads">Notify when someone downloads
                                    your files</label>
                            </div>
                            <div class="mt-2">
                                <label class="form-label">Download Threshold</label>
                                <input type="number" class="form-control" name="notify_downloads_threshold"
                                    value="<?php echo $preferences['notify_downloads_threshold'] ?? '10'; ?>" min="1"
                                    max="100" placeholder="10">
                                <small class="form-text text-muted">Only notify after this many downloads</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Feedback Notifications</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_feedback"
                                    name="notify_feedback" value="1" <?php if (($preferences['notify_feedback'] ?? '1') === '1')
                                        echo 'checked'; ?>>
                                <label class="form-check-label" for="notify_feedback">Notify when someone rates your
                                    files</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Token Notifications</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notify_tokens" name="notify_tokens"
                                    value="1" <?php if (($preferences['notify_tokens'] ?? '1') === '1')
                                        echo 'checked'; ?>>
                                <label class="form-check-label" for="notify_tokens">Notify when tokens are low</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Newsletter</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter"
                                    value="1" <?php if (($preferences['newsletter'] ?? '1') === '1')
                                        echo 'checked'; ?>>
                                <label class="form-check-label" for="newsletter">Receive newsletter and updates</label>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3"><i class="fas fa-user me-2"></i>Privacy Settings</h5>

                        <div class="mb-3">
                            <label class="form-label">Allow Feedback</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="allow_feedback"
                                    name="allow_feedback" value="1" <?php if (($preferences['allow_feedback'] ?? '1') === '1')
                                        echo 'checked'; ?>>
                                <label class="form-check-label" for="allow_feedback">Allow others to rate and comment on
                                    your files</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Search History</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="search_history"
                                    name="search_history" value="1" <?php if (($preferences['search_history'] ?? '1') === '1')
                                        echo 'checked'; ?>>
                                <label class="form-check-label" for="search_history">Save search history for better
                                    recommendations</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Activity Visibility</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="activity_visibility"
                                        id="activityPublic" value="public" <?php if (($preferences['activity_visibility'] ?? 'public') === 'public')
                                            echo 'checked'; ?>>
                                    <label class="form-check-label" for="activityPublic"><i
                                            class="fas fa-globe me-1"></i>Public</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="activity_visibility"
                                        id="activityPrivate" value="private" <?php if (($preferences['activity_visibility'] ?? 'public') === 'private')
                                            echo 'checked'; ?>>
                                    <label class="form-check-label" for="activityPrivate"><i
                                            class="fas fa-lock me-1"></i>Private</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 d-flex gap-2">
                            <a href="/eduvault/pages/change_password.php" class="btn btn-outline-secondary flex-fill"><i
                                    class="fas fa-key me-1"></i>Change
                                Password</a>
                            <a href="/eduvault/pages/view.php?id=<?php echo $user_id; ?>"
                                class="btn btn-outline-primary flex-fill"><i class="fas fa-user-edit me-1"></i>Edit
                                Profile</a>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save
                                Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>