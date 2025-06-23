<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    redirect("index.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_mime'])) {
    $mime_type = mysqli_real_escape_string($mysqli, $_POST['mime_types']);
    $extension = mysqli_real_escape_string($mysqli, $_POST['extension']);
    mysqli_query($mysqli, "INSERT INTO mimes (mime_types, extension) VALUES ('$mime_type', '$extension')");
    redirect("settings.php?success=1");
    exit();
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settings'])) {
    foreach ($_POST['settings'] as $key => $value) {
        $key = mysqli_real_escape_string($mysqli, $key);
        $value = mysqli_real_escape_string($mysqli, $value);
        mysqli_query($mysqli, "UPDATE system_settings SET 
                             setting_value = '$value',
                             updated_by = {$_SESSION['admin_id']},
                             updated_at = NOW()
                             WHERE setting_key = '$key'");
    }

    // Log the action
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_query($mysqli, "INSERT INTO activity_logs (admin_id, action, ip_address) 
                          VALUES ($admin_id, 'Settings updated', '$ip')");

    $success = true;
}

// Get all settings

$mimes = mysqli_query($mysqli, "SELECT * FROM mimes ORDER BY id DESC");
$settings = mysqli_query($mysqli, "SELECT * FROM system_settings ORDER BY id");
$settings_data = [];
while ($row = mysqli_fetch_assoc($settings)) {
    $settings_data[$row['setting_key']] = $row;
}
$title = "System Settings - Admin Panel";
require_once '../includes/admin_header.php';
?>
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-cog me-2"></i>System Settings</h2>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Settings updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h4 class="mb-3">Site Settings</h4>
                                <div class="mb-3">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" class="form-control bg-dark-body" name="settings[site_name]"
                                        value="<?php echo htmlspecialchars($settings_data['site_name']['setting_value']); ?>">
                                    <div class="form-text">
                                        <?php echo $settings_data['site_name']['description']; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Site Description</label>
                                    <textarea class="form-control bg-dark-body" name="settings[site_description]"
                                        rows="3"><?php echo htmlspecialchars($settings_data['site_description']['setting_value']); ?></textarea>
                                    <div class="form-text">
                                        <?php echo $settings_data['site_description']['description']; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h4 class="mb-3">File Settings</h4>
                                <div class="mb-3">
                                    <label class="form-label">Maximum File Size (bytes)</label>
                                    <input type="number" class="form-control bg-dark-body"
                                        name="settings[max_file_size]"
                                        value="<?php echo htmlspecialchars($settings_data['max_file_size']['setting_value']); ?>">
                                    <div class="form-text">Current:
                                        <?php echo formatBytes($settings_data['max_file_size']['setting_value']); ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Allowed File Types</label>
                                    <input type="text" class="form-control bg-dark-body"
                                        name="settings[allowed_file_types]"
                                        value="<?php echo htmlspecialchars($settings_data['allowed_file_types']['setting_value']); ?>">
                                    <div class="form-text">Comma-separated list of file extensions (e.g.,
                                        pdf,doc,docx)</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h4 class="mb-3">Book Settings</h4>
                                <div class="mb-3">
                                    <label class="form-label">Maximum Image Size (bytes)</label>
                                    <input type="number" class="form-control bg-dark-body"
                                        name="settings[max_file_size]"
                                        value="<?php echo htmlspecialchars($settings_data['max_image_size']['setting_value']); ?>">
                                    <div class="form-text">Current:
                                        <?php echo formatBytes($settings_data['max_image_size']['setting_value']); ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Allowed Image Types</label>
                                    <input type="text" class="form-control bg-dark-body"
                                        name="settings[allowed_file_types]"
                                        value="<?php echo htmlspecialchars($settings_data['allowed_image_types']['setting_value']); ?>">
                                    <div class="form-text">Comma-separated list of file extensions (e.g.,
                                        pdf,doc,docx)</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h4 class="mb-3">Display Settings</h4>
                                <div class="mb-3">
                                    <label class="form-label">Items Per Page</label>
                                    <select class="form-select input-dark" name="settings[items_per_page]">
                                        <?php
                                        $current = (int) $settings_data['items_per_page']['setting_value'];
                                        foreach ([10, 12, 15, 20, 25, 30] as $value) {
                                            echo "<option value=\"$value\"" . ($current === $value ? ' selected' : '') . ">$value</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="form-text">Number of items to display per page in listings</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h4 class="mb-3">Admin Settings</h4>
                                <div class="mb-3">
                                    <label class="form-label">Change Admin Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control bg-dark-body" id="newPassword"
                                            placeholder="Enter new password">
                                        <button class="btn btn-outline-secondary" type="button"
                                            onclick="changeAdminPassword()">
                                            Update Password
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary" value="settings">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>

            </div>
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-file me-2"></i>MIME Types</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMimeModal">
                        <i class="fas fa-plus me-1"></i> Add New MIME
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="mimesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Extension</th>
                                    <th>MIME Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($mimes)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['extension']); ?></td>
                                        <td><?php echo htmlspecialchars($row['mime_types']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php include '../modals/mimemodal.php'; ?>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
<script>
    $(document).ready(function () {
        $('#mimesTable').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            pageLength: 10,
            order: [[0, 'asc']],
        });
    });

    function changeAdminPassword() {
        const password = document.getElementById('newPassword').value;
        if (!password) {
            alert('Please enter a new password');
            return;
        }

        $.post('ajax/change_password.php', { password: password }, function (response) {
            if (response.success) {
                alert('Password updated successfully');
                document.getElementById('newPassword').value = '';
            } else {
                alert('Failed to update password: ' + response.error);
            }
        });
    }
</script>

<?php
function formatBytes($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 1) . ' ' . $units[$pow];
}
?>