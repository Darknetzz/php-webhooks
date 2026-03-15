<?php
$title = 'Users';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$currentUser = auth()->user();
$createError = $createError ?? null;
$createUsername = $createUsername ?? '';
$adminActive = 'users';
ob_start();
?>
<h1>Users</h1>
<?php require __DIR__ . '/partials/admin_nav_pills.php'; ?>
<p class="meta" style="margin-bottom: 1rem;"><a href="<?= e($baseUrl) ?>/admin">Admin</a></p>

<div class="page-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
    <span></span>
    <button type="button" class="btn btn-primary" id="btn-add-user" aria-controls="create-user-modal"><svg class="icon" aria-hidden="true"><use href="#icon-plus"/></svg> Add user</button>
</div>

<?php if (empty($users)): ?>
    <div class="empty-state">
        <p>No users yet. Add a user to get started.</p>
        <button type="button" class="btn btn-primary" id="btn-add-user-empty">Add user</button>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th class="table-cell-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= e($u->username) ?></td>
                        <td><?= e($u->role) ?></td>
                        <td><?= e($u->created_at) ?></td>
                        <td class="table-cell-actions card-actions">
                            <a href="<?= e($baseUrl) ?>/admin/users/<?= $u->id ?>/edit" class="btn btn-ghost" style="padding: 0.35rem 0.6rem; font-size: 0.85rem;"><svg class="icon" aria-hidden="true"><use href="#icon-edit"/></svg> Edit</a>
                            <?php if ($currentUser && $currentUser->isSuperAdmin() && $u->id !== $currentUser->id): ?>
                                <?php $isLastSuperadmin = $u->isSuperAdmin() && \App\UserRepository::countSuperAdmins() <= 1; ?>
                                <?php if (!$isLastSuperadmin): ?>
                                    <form method="post" action="<?= e($baseUrl) ?>/admin/users/<?= $u->id ?>/delete" style="display: inline;" onsubmit="return confirm('Delete user <?= e(addslashes($u->username)) ?>? Their webhooks will be deleted too.');">
                                        <button type="submit" class="btn btn-danger" style="padding: 0.35rem 0.6rem; font-size: 0.85rem;"><svg class="icon" aria-hidden="true"><use href="#icon-trash"/></svg> Delete</button>
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

<!-- Create user modal -->
<div class="modal-overlay" id="create-user-modal" role="dialog" aria-modal="true" aria-labelledby="create-user-modal-title">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2 id="create-user-modal-title">Create user</h2>
            <button type="button" class="modal-close" data-close="create-user-modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <?php if ($createError): ?>
                <div class="error-msg" style="margin-bottom: 1rem;"><?= e($createError) ?></div>
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
                        <option value="<?= e(\App\User::ROLE_USER) ?>">User</option>
                        <option value="<?= e(\App\User::ROLE_ADMIN) ?>">Admin</option>
                        <option value="<?= e(\App\User::ROLE_SUPERADMIN) ?>">Superadmin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Create user</button>
                <button type="button" class="btn btn-ghost" data-close="create-user-modal">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    function openModal(id) {
        document.getElementById(id).classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('is-open');
        document.body.style.overflow = '';
    }
    document.querySelectorAll('[data-close="create-user-modal"]').forEach(function (btn) {
        btn.addEventListener('click', function () { closeModal('create-user-modal'); });
    });
    var overlay = document.getElementById('create-user-modal');
    if (overlay) overlay.addEventListener('click', function () { closeModal('create-user-modal'); });
    document.querySelectorAll('#btn-add-user, #btn-add-user-empty').forEach(function (btn) {
        if (btn) btn.onclick = function () { openModal('create-user-modal'); };
    });
    <?php if ($createError): ?>
    openModal('create-user-modal');
    <?php endif; ?>
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
