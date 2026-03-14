<?php
$title = 'Profile';
$config = config();
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
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
