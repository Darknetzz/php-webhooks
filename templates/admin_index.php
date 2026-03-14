<?php
$title = 'Admin';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$currentUser = auth()->user();
$adminActive = 'index';
ob_start();
?>
<h1>Admin</h1>
<?php require __DIR__ . '/partials/admin_nav_pills.php'; ?>
<p class="meta" style="margin-bottom: 1rem;">Manage webhooks and users.</p>
<div class="admin-panel-cards">
    <a href="<?= e($baseUrl) ?>/admin/all-webhooks" class="card admin-panel-card" style="display: block; text-decoration: none; color: inherit;">
        <h2 style="font-size: 1.1rem; margin: 0 0 0.5rem;">Webhooks</h2>
        <p class="meta" style="margin: 0;">View all webhooks and their owners.</p>
    </a>
    <a href="<?= e($baseUrl) ?>/admin/users" class="card admin-panel-card" style="display: block; text-decoration: none; color: inherit;">
        <h2 style="font-size: 1.1rem; margin: 0 0 0.5rem;">Users</h2>
        <p class="meta" style="margin: 0;">Create and manage user accounts.</p>
    </a>
</div>
<p style="margin-top: 1.5rem;"><a href="<?= e($baseUrl) ?>/" class="btn btn-ghost">Webhooks</a></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
