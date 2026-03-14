<?php
$title = 'Profile';
$config = config();
ob_start();
?>
<h1>Profile</h1>
<div class="card">
    <p class="meta">Logged in as <strong><?= e($user->username) ?></strong>. Role: <?= e($user->role) ?>.</p>
    <p style="margin: 0; color: var(--muted);">Profile settings can be extended here (e.g. change password, avatar).</p>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
