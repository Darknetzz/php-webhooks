<?php
$title = 'Webhooks';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$webhookBaseUrl = rtrim(webhook_base_url(), '/');
$createError = $createError ?? null;
$createName = $createName ?? '';
$createSlug = $createSlug ?? '';
$createDescription = $createDescription ?? '';
$createIsPublic = isset($createIsPublic) ? $createIsPublic : false;
$createRequestsPublic = $createRequestsPublic ?? false;
$createSlugFromName = $createSlugFromName ?? true;
$createResponseStatusCode = $createResponseStatusCode ?? 200;
$createResponseHeaders = $createResponseHeaders ?? '';
$createResponseBody = $createResponseBody ?? '';
$createAllowedMethods = $createAllowedMethods ?? [];
$allowedMethodOptions = webhook_allowed_method_options();
ob_start();
?>
<div class="page-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
    <h1 style="margin: 0;">Webhooks</h1>
    <button type="button" class="btn btn-primary btn-add-webhook" id="btn-add-webhook" aria-controls="create-modal"><svg class="icon" aria-hidden="true"><use href="#icon-plus"/></svg> Add webhook</button>
</div>

<?php if (empty($webhooks)): ?>
    <div class="empty-state">
        <p>You have no webhooks yet. Create one to receive HTTP requests at a unique URL.</p>
        <button type="button" class="btn btn-primary btn-add-webhook" id="btn-add-webhook-empty"><svg class="icon" aria-hidden="true"><use href="#icon-plus"/></svg> Add webhook</button>
    </div>
<?php else: ?>
    <div class="webhook-cards">
        <?php foreach ($webhooks as $w):
            $requestCount = (int) (($requestCounts ?? [])[$w->id] ?? 0);
            ?>
            <div class="card webhook-card" data-id="<?= (int) $w->id ?>" data-name="<?= e($w->name) ?>" data-slug="<?= e($w->slug) ?>" data-description="<?= e($w->description) ?>" data-is-public="<?= $w->is_public ? '1' : '0' ?>" data-requests-public="<?= $w->requests_public ? '1' : '0' ?>" data-response-status-code="<?= (int) $w->response_status_code ?>" data-response-headers="<?= e($w->response_headers) ?>" data-response-body="<?= e($w->response_body) ?>" data-allowed-methods="<?= e($w->allowed_methods ?? '') ?>">
                <h3><?= e($w->name) ?></h3>
                <?php if ($w->description): ?>
                    <p class="meta"><?= e($w->description) ?></p>
                <?php endif; ?>
                <?php $webhookUrl = $webhookBaseUrl . '/w/' . $w->slug; require __DIR__ . '/partials/webhook_url_block.php'; ?>
                <p class="meta"><?php $isPublic = (bool) $w->is_public; $publicLabel = 'Listed publicly'; require __DIR__ . '/partials/visibility_label.php'; ?><?= $w->requests_public ? ' · Requests public' : '' ?> · <?php $date = $w->created_at; $label = 'Created '; require __DIR__ . '/partials/created_date.php'; ?> · <?php $count = $requestCount; $url = $baseUrl . '/admin/webhooks/' . $w->id . '/requests'; require __DIR__ . '/partials/request_count.php'; ?></p>
                <div class="card-actions">
                    <a href="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/requests" class="btn btn-ghost btn-icon-only" aria-label="View requests" title="View requests"><svg class="icon" aria-hidden="true"><use href="#icon-eye"/></svg></a>
                    <button type="button" class="btn btn-ghost btn-icon-only btn-edit-webhook" aria-label="Edit"><svg class="icon" aria-hidden="true"><use href="#icon-edit"/></svg></button>
                    <form method="post" action="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/delete" style="display: inline;" onsubmit="return confirm('Delete this webhook and all its request history?');">
                        <button type="submit" class="btn btn-danger btn-icon-only" aria-label="Delete"><svg class="icon" aria-hidden="true"><use href="#icon-trash"/></svg></button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/partials/create_webhook_modal.php'; ?>

<?php require __DIR__ . '/partials/edit_webhook_modal.php'; ?>
<?php require __DIR__ . '/partials/edit_webhook_modal_script.php'; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
