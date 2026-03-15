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
        <div class="card webhook-card" data-id="<?= (int) $w->id ?>" data-name="<?= e($w->name) ?>" data-slug="<?= e($w->slug) ?>" data-description="<?= e($w->description) ?>" data-is-public="<?= $w->is_public ? '1' : '0' ?>" data-requests-public="<?= $w->requests_public ? '1' : '0' ?>" data-response-status-code="<?= (int) $w->response_status_code ?>" data-response-headers="<?= e($w->response_headers) ?>" data-response-body="<?= e($w->response_body) ?>" data-allowed-methods="<?= e($w->allowed_methods ?? '') ?>">
            <h3><?= e($w->name) ?></h3>
            <?php $webhookUrl = $webhookBaseUrl . '/w/' . $w->slug; require __DIR__ . '/partials/webhook_url_block.php'; ?>
            <div class="card-actions">
                <a href="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/requests" class="btn btn-ghost btn-icon-only" aria-label="View requests" title="View requests"><svg class="icon" aria-hidden="true"><use href="#icon-eye"/></svg></a>
                <button type="button" class="btn btn-ghost btn-icon-only btn-edit-webhook" aria-label="Edit"><svg class="icon" aria-hidden="true"><use href="#icon-edit"/></svg></button>
            </div>
        </div>
    <?php endforeach; ?>
    <?php require __DIR__ . '/partials/edit_webhook_modal.php'; ?>
    <?php require __DIR__ . '/partials/edit_webhook_modal_script.php'; ?>
    <?php if (count($webhooks) > 5): ?>
        <p><a href="<?= e($baseUrl) ?>/admin/webhooks">View all (<?= count($webhooks) ?>)</a></p>
    <?php endif; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
