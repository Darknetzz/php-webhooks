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
                <p class="meta"><?= $w->is_public ? 'Listed publicly' : 'Private' ?><?= $w->requests_public ? ' · Requests public' : '' ?> · Created <?= e($w->created_at) ?><?php if ($requestCount !== 0): ?> · <strong><?= $requestCount ?></strong> request<?= $requestCount === 1 ? '' : 's' ?><?php endif; ?></p>
                <div class="card-actions">
                    <a href="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/requests" class="btn btn-ghost"><svg class="icon" aria-hidden="true"><use href="#icon-requests"/></svg> View requests<?php if ($requestCount): ?> (<?= $requestCount ?>)<?php endif; ?></a>
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

<!-- Edit modal -->
<div class="modal-overlay" id="edit-modal" role="dialog" aria-modal="true" aria-labelledby="edit-modal-title">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2 id="edit-modal-title">Edit webhook</h2>
            <button type="button" class="modal-close" data-close="edit-modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="post" id="edit-webhook-form" action="">
                <div class="form-group">
                    <label for="edit-name">Name</label>
                    <input type="text" id="edit-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit-slug">Slug (URL path)</label>
                    <input type="text" id="edit-slug" name="slug" pattern="[a-zA-Z0-9_-]+">
                    <div class="hint"><?= e($webhookBaseUrl) ?>/w/<strong id="edit-slug-preview"></strong></div>
                </div>
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <textarea id="edit-description" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_public" value="1" id="edit-is_public">
                        List on public page
                    </label>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="requests_public" value="1" id="edit-requests_public">
                        Show requests publicly
                    </label>
                </div>
                <div class="form-section">
                    <h3 class="form-section-title">Allowed methods</h3>
                    <?php $selectedMethods = []; $specifyToggleId = 'edit-specify-allowed-methods'; require __DIR__ . '/partials/allowed_methods_field.php'; ?>
                </div>
                <div class="form-section">
                    <h3 class="form-section-title">Response (optional)</h3>
                    <?php require __DIR__ . '/partials/response_variables_hint.php'; ?>
                    <div class="form-group">
                        <label for="edit-response_status_code">Status code</label>
                        <input type="number" id="edit-response_status_code" name="response_status_code" min="100" max="599">
                    </div>
                    <div class="form-group">
                        <label for="edit-response_headers">Response headers (JSON)</label>
                        <textarea id="edit-response_headers" name="response_headers" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-response_body">Response body</label>
                        <textarea id="edit-response_body" name="response_body" rows="3"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-ghost" data-close="edit-modal">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var baseUrl = <?= json_encode($baseUrl) ?>;
    function openModal(id) {
        document.getElementById(id).classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('is-open');
        document.body.style.overflow = '';
    }
    document.querySelectorAll('[data-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            closeModal(this.getAttribute('data-close'));
        });
    });
    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function () {
            closeModal(this.id);
        });
    });

    document.querySelectorAll('.btn-edit-webhook').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var card = this.closest('.webhook-card');
            if (!card) return;
            var id = card.dataset.id;
            document.getElementById('edit-name').value = card.dataset.name || '';
            document.getElementById('edit-slug').value = card.dataset.slug || '';
            document.getElementById('edit-description').value = card.dataset.description || '';
            document.getElementById('edit-is_public').checked = card.dataset.isPublic === '1';
            document.getElementById('edit-requests_public').checked = card.dataset.requestsPublic === '1';
            document.getElementById('edit-response_status_code').value = card.dataset.responseStatusCode || '200';
            document.getElementById('edit-response_headers').value = card.dataset.responseHeaders || '';
            document.getElementById('edit-response_body').value = card.dataset.responseBody || '';
            document.getElementById('edit-slug-preview').textContent = card.dataset.slug || '';
            var allowed = (card.dataset.allowedMethods || '').split(',').map(function (m) { return m.trim(); }).filter(Boolean);
            document.querySelectorAll('#edit-modal input[name="allowed_methods[]"]').forEach(function (cb) {
                cb.checked = allowed.indexOf(cb.value) !== -1;
            });
            var specifyToggle = document.getElementById('edit-specify-allowed-methods');
            if (specifyToggle) {
                specifyToggle.checked = allowed.length > 0;
                specifyToggle.dispatchEvent(new Event('change'));
            }
            document.getElementById('edit-webhook-form').action = baseUrl + '/admin/webhooks/' + id + '/edit';
            openModal('edit-modal');
        });
    });
    document.getElementById('edit-slug').addEventListener('input', function () {
        document.getElementById('edit-slug-preview').textContent = this.value || '';
    });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
