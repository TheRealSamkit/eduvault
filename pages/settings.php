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

// Language and timezone options
$languages = [
    'en' => 'English',
    'hi' => 'Hindi',
    'es' => 'Spanish',
    'fr' => 'French',
    'de' => 'German',
    'zh' => 'Chinese',
    'ja' => 'Japanese',
    'ar' => 'Arabic',
];
$timezones = [
    'Asia/Kolkata' => 'Asia/Kolkata (IST)',
    'Asia/Shanghai' => 'Asia/Shanghai',
    'Asia/Tokyo' => 'Asia/Tokyo',
    'Europe/London' => 'Europe/London',
    'Europe/Berlin' => 'Europe/Berlin',
    'America/New_York' => 'America/New_York',
    'America/Los_Angeles' => 'America/Los_Angeles',
    'UTC' => 'UTC',
];

// Fetch user settings
$query = "SELECT name, email, theme_preference, notification_email, language, profile_visibility, download_confirm, timezone FROM users WHERE id = ?";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = in_array($_POST['theme_preference'], ['light', 'dark', 'system']) ? $_POST['theme_preference'] : 'system';
    $notify = isset($_POST['notification_email']) ? 1 : 0;
    $language = array_key_exists($_POST['language'], $languages) ? $_POST['language'] : 'en';
    $profile_visibility = in_array($_POST['profile_visibility'], ['public', 'private']) ? $_POST['profile_visibility'] : 'public';
    $download_confirm = isset($_POST['download_confirm']) ? 1 : 0;
    $timezone = array_key_exists($_POST['timezone'], $timezones) ? $_POST['timezone'] : 'Asia/Kolkata';
    $update = "UPDATE users SET theme_preference = ?, notification_email = ?, language = ?, profile_visibility = ?, download_confirm = ?, timezone = ? WHERE id = ?";
    $stmt = mysqli_prepare($mysqli, $update);
    mysqli_stmt_bind_param($stmt, 'sissisi', $theme, $notify, $language, $profile_visibility, $download_confirm, $timezone, $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['toasts'][] = ['type' => 'success', 'message' => 'Settings updated successfully.'];
        $user['theme_preference'] = $theme;
        $user['notification_email'] = $notify;
        $user['language'] = $language;
        $user['profile_visibility'] = $profile_visibility;
        $user['download_confirm'] = $download_confirm;
        $user['timezone'] = $timezone;
    } else {
        $_SESSION['toasts'][] = ['type' => 'error', 'message' => 'Failed to update settings.'];
    }
    mysqli_stmt_close($stmt);
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
                                        value="light" <?php if ($user['theme_preference'] === 'light')
                                            echo 'checked'; ?>>
                                    <label class="form-check-label" for="themeLight"><i
                                            class="fas fa-sun me-1"></i>Light</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="theme_preference" id="themeDark"
                                        value="dark" <?php if ($user['theme_preference'] === 'dark')
                                            echo 'checked'; ?>>
                                    <label class="form-check-label" for="themeDark"><i
                                            class="fas fa-moon me-1"></i>Dark</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="theme_preference"
                                        id="themeSystem" value="system" <?php if ($user['theme_preference'] === 'system')
                                            echo 'checked'; ?>>
                                    <label class="form-check-label" for="themeSystem"><i
                                            class="fas fa-desktop me-1"></i>System</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Language</label>
                            <select class="form-select" name="language">
                                <?php foreach ($languages as $code => $label): ?>
                                    <option value="<?php echo $code; ?>" <?php if ($user['language'] === $code)
                                           echo 'selected'; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Timezone</label>
                            <select class="form-select" name="timezone">
                                <?php foreach ($timezones as $tz => $label): ?>
                                    <option value="<?php echo $tz; ?>" <?php if ($user['timezone'] === $tz)
                                           echo 'selected'; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profile Visibility</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="profile_visibility"
                                        id="profilePublic" value="public" <?php if ($user['profile_visibility'] === 'public')
                                            echo 'checked'; ?>>
                                    <label class="form-check-label" for="profilePublic"><i
                                            class="fas fa-globe me-1"></i>Public</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="profile_visibility"
                                        id="profilePrivate" value="private" <?php if ($user['profile_visibility'] === 'private')
                                            echo 'checked'; ?>>
                                    <label class="form-check-label" for="profilePrivate"><i
                                            class="fas fa-lock me-1"></i>Private</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Notifications</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notification_email"
                                    name="notification_email" value="1" <?php if ($user['notification_email'])
                                        echo 'checked'; ?>>
                                <label class="form-check-label" for="notification_email">Receive important
                                    updates
                                    via
                                    email</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Download Confirmation</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="download_confirm"
                                    name="download_confirm" value="1" <?php if ($user['download_confirm'])
                                        echo 'checked'; ?>>
                                <label class="form-check-label" for="download_confirm">Show confirmation before
                                    downloading files</label>
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