<?php
$title = 'Webhooks';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$webhookBaseUrl = rtrim(webhook_base_url(), '/');
ob_start();
?>
<h1>Public Webhooks</h1>
<p style="color: var(--muted); margin-bottom: 1.5rem;">Send HTTP requests to these endpoints. Log in to create and manage your own webhooks.</p>
<?php if (empty($webhooks)): ?>
    <div class="empty-state">
        <p>No public webhooks yet.</p>
        <p><a href="<?= e($baseUrl) ?>/login" class="btn btn-primary">Log in</a> to create one.</p>
    </div>
<?php else: ?>
    <?php foreach ($webhooks as $w): ?>
        <div class="card">
            <h3><?= e($w->name) ?></h3>
            <?php if ($w->description): ?>
                <p class="meta"><?= e($w->description) ?></p>
            <?php endif; ?>
            <?php $webhookUrl = $webhookBaseUrl . '/w/' . $w->slug; require __DIR__ . '/partials/webhook_url_block.php'; ?>
            <p class="meta">POST, GET, or any method — requests are logged for the owner.</p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
