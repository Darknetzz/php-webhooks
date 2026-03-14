<?php
$title = 'Profile';
$config = config();
$passwordError = $passwordError ?? null;
$passwordSuccess = $passwordSuccess ?? false;
ob_start();
?>
<h1>Profile</h1>
<div class="card profile-header">
    <div class="profile-header-avatar">
        <span class="user-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($user->username, 0, 1))) ?></span>
    </div>
    <div class="table-wrap">
        <table>
            <tbody>
                <tr>
                    <th>Username</th>
                    <td><?= e($user->username) ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td><?= e($user->role) ?></td>
                </tr>
                <tr>
                    <th>Member since</th>
                    <td><?= e($user->created_at) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <h2 style="font-size: 1.1rem; margin-bottom: 0.75rem;">Change password</h2>
    <?php if ($passwordSuccess): ?>
        <div class="flash" style="margin-bottom: 1rem;">Password updated successfully.</div>
    <?php endif; ?>
    <?php if ($passwordError): ?>
        <div class="error-msg" style="margin-bottom: 1rem;"><?= e($passwordError) ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <input type="hidden" name="change_password" value="1">
        <div class="form-group">
            <label for="current_password">Current password</label>
            <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
        </div>
        <div class="form-group">
            <label for="new_password">New password</label>
            <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
        </div>
        <div class="form-group">
            <label for="new_password_confirm">Confirm new password</label>
            <input type="password" id="new_password_confirm" name="new_password_confirm" required minlength="8" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary">Update password</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
