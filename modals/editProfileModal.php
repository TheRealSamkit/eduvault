<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 text-center">
                    <img src="../<?php echo htmlspecialchars($user['avatar_path'] ?? '../uploads/avatars/default.png'); ?>"
                        class="rounded-circle img-thumbnail bg-dark" width="80" alt="User Avatar">
                </div>
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control bg-dark-body"
                        value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control bg-dark-body"
                        value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control bg-dark-body"
                        value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control bg-dark-body"
                        value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Avatar</label>
                    <input type="file" name="avatar" class="form-control bg-dark-body" accept=".jpg,.jpeg,.png">
                    <div class="form-text">Leave empty to keep current avatar</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="save_profile" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>