<?php
$title = 'Edit user';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$editError = $editError ?? null;
ob_start();
?>
<h1>Edit user</h1>
<p class="meta" style="margin-bottom: 1rem;"><a href="<?= e($baseUrl) ?>/admin">Admin</a> · <a href="<?= e($baseUrl) ?>/admin/users">Users</a></p>
<?php if ($editError): ?>
    <div class="error-msg"><?= e($editError) ?></div>
<?php endif; ?>
<div class="card" style="max-width: 500px;">
    <form method="post" action="">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required value="<?= e($editUser->username) ?>">
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="<?= e(\App\User::ROLE_USER) ?>" <?= $editUser->role === \App\User::ROLE_USER ? 'selected' : '' ?>>User</option>
                <option value="<?= e(\App\User::ROLE_ADMIN) ?>" <?= $editUser->role === \App\User::ROLE_ADMIN ? 'selected' : '' ?>>Admin</option>
                <option value="<?= e(\App\User::ROLE_SUPERADMIN) ?>" <?= $editUser->role === \App\User::ROLE_SUPERADMIN ? 'selected' : '' ?>>Superadmin</option>
            </select>
        </div>
        <div class="form-group">
            <label for="password">New password (leave blank to keep current)</label>
            <input type="password" id="password" name="password" minlength="8" placeholder="Min 8 characters">
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="<?= e($baseUrl) ?>/admin/users" class="btn btn-ghost">Cancel</a>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
