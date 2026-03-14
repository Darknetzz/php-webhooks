<?php
$title = 'Users';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$currentUser = auth()->user();
$createError = $createError ?? null;
$createUsername = $createUsername ?? '';
ob_start();
?>
<h1>Users</h1>
<p class="meta" style="margin-bottom: 1rem;"><a href="<?= e($baseUrl) ?>/admin">Admin</a></p>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.1rem; margin-bottom: 0.75rem;">Create user</h2>
    <?php if ($createError): ?>
        <div class="error-msg"><?= e($createError) ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="form-group">
            <label for="create_username">Username</label>
            <input type="text" id="create_username" name="create_username" required value="<?= e($createUsername) ?>">
        </div>
        <div class="form-group">
            <label for="create_password">Password</label>
            <input type="password" id="create_password" name="create_password" required minlength="8" placeholder="Min 8 characters">
        </div>
        <div class="form-group">
            <label for="create_role">Role</label>
            <select id="create_role" name="create_role">
                <option value="<?= e(\App\User::ROLE_ADMIN) ?>">Admin</option>
                <option value="<?= e(\App\User::ROLE_SUPERADMIN) ?>">Superadmin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create user</button>
    </form>
</div>

<?php if (empty($users)): ?>
    <div class="empty-state">
        <p>No users yet.</p>
    </div>
<?php else: ?>
    <h2 style="font-size: 1.1rem; margin: 0 0 0.75rem;">All users</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= e($u->username) ?></td>
                        <td><?= e($u->role) ?></td>
                        <td><?= e($u->created_at) ?></td>
                        <td class="card-actions">
                            <a href="<?= e($baseUrl) ?>/admin/users/<?= $u->id ?>/edit" class="btn btn-ghost" style="padding: 0.35rem 0.6rem; font-size: 0.85rem;">Edit</a>
                            <?php if ($currentUser && $currentUser->isSuperAdmin() && $u->id !== $currentUser->id): ?>
                                <?php $isLastSuperadmin = $u->isSuperAdmin() && \App\UserRepository::countSuperAdmins() <= 1; ?>
                                <?php if (!$isLastSuperadmin): ?>
                                    <form method="post" action="<?= e($baseUrl) ?>/admin/users/<?= $u->id ?>/delete" style="display: inline;" onsubmit="return confirm('Delete user <?= e(addslashes($u->username)) ?>? Their webhooks will be deleted too.');">
                                        <button type="submit" class="btn btn-danger" style="padding: 0.35rem 0.6rem; font-size: 0.85rem;">Delete</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
