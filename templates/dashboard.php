<?php
$title = 'Dashboard';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$webhookBaseUrl = rtrim(webhook_base_url(), '/');
ob_start();
?>
<h1>Dashboard</h1>
<?php if (empty($webhooks)): ?>
    <div class="empty-state">
        <p>You have no webhooks yet. Create one to get started and receive HTTP requests at a unique URL.</p>
        <a href="<?= e($baseUrl) ?>/admin/webhooks" class="btn btn-primary">Create webhook</a>
    </div>
<?php else: ?>
    <p style="color: var(--muted); margin-bottom: 1.5rem;">Your webhooks. <a href="<?= e($baseUrl) ?>/admin/webhooks">Manage all</a></p>
    <?php foreach (array_slice($webhooks, 0, 5) as $w): ?>
        <div class="card">
            <h3><?= e($w->name) ?></h3>
            <?php $webhookUrl = $webhookBaseUrl . '/w/' . $w->slug; require __DIR__ . '/partials/webhook_url_block.php'; ?>
            <div class="card-actions">
                <a href="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/requests" class="btn btn-ghost">View requests</a>
                <a href="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/edit" class="btn btn-ghost" aria-label="Edit"><svg class="icon" aria-hidden="true"><use href="#icon-edit"/></svg></a>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (count($webhooks) > 5): ?>
        <p><a href="<?= e($baseUrl) ?>/admin/webhooks">View all (<?= count($webhooks) ?>)</a></p>
    <?php endif; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
