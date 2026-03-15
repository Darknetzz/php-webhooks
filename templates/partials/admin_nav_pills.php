<?php
$baseUrl = rtrim(base_url(), '/');
$adminActive = $adminActive ?? 'index';
?>
<nav class="nav-pills" aria-label="Admin sections">
    <a href="<?= e($baseUrl) ?>/admin" class="<?= $adminActive === 'index' ? 'active' : '' ?>">Overview</a>
    <a href="<?= e($baseUrl) ?>/admin/all-webhooks" class="<?= $adminActive === 'webhooks' ? 'active' : '' ?>">All webhooks</a>
    <a href="<?= e($baseUrl) ?>/admin/users" class="<?= $adminActive === 'users' ? 'active' : '' ?>">Users</a>
    <a href="<?= e($baseUrl) ?>/admin/settings" class="<?= $adminActive === 'settings' ? 'active' : '' ?>">Settings</a>
</nav>
