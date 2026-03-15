<?php
// Edit user modal. Expects $baseUrl. Optional: $editUser (when reopening after error), $editError.
$editUser = $editUser ?? null;
$editError = $editError ?? null;
?>
<div class="modal-overlay" id="edit-user-modal" role="dialog" aria-modal="true" aria-labelledby="edit-user-modal-title">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2 id="edit-user-modal-title">Edit user</h2>
            <button type="button" class="modal-close" data-close="edit-user-modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <?php if ($editError): ?>
                <div class="error-msg" style="margin-bottom: 1rem;"><?= e($editError) ?></div>
            <?php endif; ?>
            <form method="post" id="edit-user-form" action="">
                <div class="form-group">
                    <label for="edit-username">Username</label>
                    <input type="text" id="edit-username" name="username" required value="<?= $editUser ? e($editUser->username) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="edit-role">Role</label>
                    <select id="edit-role" name="role">
                        <option value="<?= e(\App\User::ROLE_USER) ?>" <?= ($editUser && $editUser->role === \App\User::ROLE_USER) ? 'selected' : '' ?>>User</option>
                        <option value="<?= e(\App\User::ROLE_ADMIN) ?>" <?= ($editUser && $editUser->role === \App\User::ROLE_ADMIN) ? 'selected' : '' ?>>Admin</option>
                        <option value="<?= e(\App\User::ROLE_SUPERADMIN) ?>" <?= ($editUser && $editUser->role === \App\User::ROLE_SUPERADMIN) ? 'selected' : '' ?>>Superadmin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-password">New password (leave blank to keep current)</label>
                    <input type="password" id="edit-password" name="password" minlength="8" placeholder="Min 8 characters">
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-ghost" data-close="edit-user-modal">Cancel</button>
            </form>
        </div>
    </div>
</div>
