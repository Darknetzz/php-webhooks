<?php
$title = 'Webhooks';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$webhookBaseUrl = rtrim(webhook_base_url(), '/');
$createError = $createError ?? null;
$createName = $createName ?? '';
$createSlug = $createSlug ?? '';
$createDescription = $createDescription ?? '';
$createIsPublic = isset($createIsPublic) ? $createIsPublic : true;
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
        <?php foreach ($webhooks as $w): ?>
            <div class="card webhook-card" data-id="<?= (int) $w->id ?>" data-name="<?= e($w->name) ?>" data-slug="<?= e($w->slug) ?>" data-description="<?= e($w->description) ?>" data-is-public="<?= $w->is_public ? '1' : '0' ?>" data-response-status-code="<?= (int) $w->response_status_code ?>" data-response-headers="<?= e($w->response_headers) ?>" data-response-body="<?= e($w->response_body) ?>" data-allowed-methods="<?= e($w->allowed_methods ?? '') ?>">
                <h3><?= e($w->name) ?></h3>
                <?php if ($w->description): ?>
                    <p class="meta"><?= e($w->description) ?></p>
                <?php endif; ?>
                <?php $webhookUrl = $webhookBaseUrl . '/w/' . $w->slug; require __DIR__ . '/partials/webhook_url_block.php'; ?>
                <p class="meta"><?= $w->is_public ? 'Public' : 'Private' ?> · Created <?= e($w->created_at) ?></p>
                <div class="card-actions">
                    <a href="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/requests" class="btn btn-ghost"><svg class="icon" aria-hidden="true"><use href="#icon-requests"/></svg> View requests</a>
                    <button type="button" class="btn btn-ghost btn-edit-webhook"><svg class="icon" aria-hidden="true"><use href="#icon-edit"/></svg> Edit</button>
                    <form method="post" action="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/delete" style="display: inline;" onsubmit="return confirm('Delete this webhook and all its request history?');">
                        <button type="submit" class="btn btn-danger"><svg class="icon" aria-hidden="true"><use href="#icon-trash"/></svg> Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Create modal -->
<div class="modal-overlay" id="create-modal" role="dialog" aria-modal="true" aria-labelledby="create-modal-title" <?= $createError ? ' aria-describedby="create-error"' : '' ?>>
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2 id="create-modal-title">Create webhook</h2>
            <button type="button" class="modal-close" data-close="create-modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <?php if ($createError): ?>
                <div class="error-msg" id="create-error"><?= e($createError) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= e($baseUrl) ?>/admin/webhooks" id="create-webhook-form">
                <div class="form-section">
                    <div class="form-group">
                        <label for="create-name">Name</label>
                        <input type="text" id="create-name" name="name" required placeholder="My API hook" value="<?= e($createName) ?>">
                    </div>
                    <div class="form-group">
                        <label for="create-description">Description (optional)</label>
                        <textarea id="create-description" name="description" placeholder="What this webhook is for"><?= e($createDescription) ?></textarea>
                    </div>
                </div>
                <div class="form-section">
                    <h3 class="form-section-title">URL</h3>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="slug_from_name" id="create-slug_from_name" value="1" <?= $createSlugFromName ? 'checked' : '' ?>>
                            Create slug from name
                        </label>
                    </div>
                    <div class="form-group" id="create-slug-field-wrap">
                        <label for="create-slug">Custom slug (optional)</label>
                        <input type="text" id="create-slug" name="slug" placeholder="my-api-hook" pattern="[a-zA-Z0-9_-]+" value="<?= e($createSlug) ?>">
                        <div class="hint">URL: <strong><?= e($webhookBaseUrl) ?>/w/<span id="create-slug-preview">my-api-hook</span></strong></div>
                    </div>
                    <div class="form-group" id="create-random-slug-hint" style="display: none;">
                        <div class="hint">A random slug will be generated.</div>
                    </div>
                </div>
                <div class="form-section">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_public" value="1" <?= $createIsPublic ? 'checked' : '' ?>>
                            List on public page
                        </label>
                    </div>
                </div>
                <div class="form-section">
                    <h3 class="form-section-title">Allowed methods</h3>
                    <?php $selectedMethods = $createAllowedMethods; require __DIR__ . '/partials/allowed_methods_field.php'; ?>
                </div>
                <div class="form-section">
                    <h3 class="form-section-title">Response (optional)</h3>
                    <div class="form-group">
                        <label for="create-response_status_code">Status code</label>
                        <input type="number" id="create-response_status_code" name="response_status_code" min="100" max="599" value="<?= (int) $createResponseStatusCode ?>">
                    </div>
                    <div class="form-group">
                        <label for="create-response_headers">Response headers (JSON)</label>
                        <textarea id="create-response_headers" name="response_headers" rows="2" placeholder='{"Content-Type": "application/json"}'><?= e($createResponseHeaders) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="create-response_body">Response body</label>
                        <textarea id="create-response_body" name="response_body" rows="3" placeholder="Leave empty for default"><?= e($createResponseBody) ?></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
                <button type="button" class="btn btn-ghost" data-close="create-modal">Cancel</button>
            </form>
        </div>
    </div>
</div>

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
                <div class="form-section">
                    <h3 class="form-section-title">Allowed methods</h3>
                    <?php $selectedMethods = []; require __DIR__ . '/partials/allowed_methods_field.php'; ?>
                </div>
                <div class="form-section">
                    <h3 class="form-section-title">Response (optional)</h3>
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
    function addOpenHandlers() {
        document.querySelectorAll('#btn-add-webhook, #btn-add-webhook-empty').forEach(function (btn) {
            btn.onclick = function () { openModal('create-modal'); };
        });
    }
    addOpenHandlers();

    document.querySelectorAll('.btn-edit-webhook').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var card = this.closest('.webhook-card');
            if (!card) return;
            var id = card.dataset.id;
            document.getElementById('edit-name').value = card.dataset.name || '';
            document.getElementById('edit-slug').value = card.dataset.slug || '';
            document.getElementById('edit-description').value = card.dataset.description || '';
            document.getElementById('edit-is_public').checked = card.dataset.isPublic === '1';
            document.getElementById('edit-response_status_code').value = card.dataset.responseStatusCode || '200';
            document.getElementById('edit-response_headers').value = card.dataset.responseHeaders || '';
            document.getElementById('edit-response_body').value = card.dataset.responseBody || '';
            document.getElementById('edit-slug-preview').textContent = card.dataset.slug || '';
            var allowed = (card.dataset.allowedMethods || '').split(',').map(function (m) { return m.trim(); }).filter(Boolean);
            document.querySelectorAll('#edit-modal input[name="allowed_methods[]"]').forEach(function (cb) {
                cb.checked = allowed.indexOf(cb.value) !== -1;
            });
            document.getElementById('edit-webhook-form').action = baseUrl + '/admin/webhooks/' + id + '/edit';
            openModal('edit-modal');
        });
    });
    document.getElementById('edit-slug').addEventListener('input', function () {
        document.getElementById('edit-slug-preview').textContent = this.value || '';
    });

    var slugify = function (s) {
        return s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || '';
    };
    var createSlugFromName = document.getElementById('create-slug_from_name');
    var createName = document.getElementById('create-name');
    var createSlug = document.getElementById('create-slug');
    var createSlugPreview = document.getElementById('create-slug-preview');
    var createSlugFieldWrap = document.getElementById('create-slug-field-wrap');
    var createRandomSlugHint = document.getElementById('create-random-slug-hint');
    function updateCreateSlugVisibility() {
        var fromName = createSlugFromName && createSlugFromName.checked;
        if (createSlugFieldWrap) createSlugFieldWrap.style.display = fromName ? 'block' : 'none';
        if (createRandomSlugHint) createRandomSlugHint.style.display = fromName ? 'none' : 'block';
        if (!fromName && createSlug) createSlug.value = '';
    }
    function updateCreatePreview() {
        if (!createSlugPreview) return;
        var fromName = createSlugFromName && createSlugFromName.checked;
        var slug = (createSlug && createSlug.value.trim()) || '';
        if (!fromName && !slug) { createSlugPreview.textContent = '(random)'; return; }
        if (!slug && createName && createName.value.trim()) {
            slug = slugify(createName.value.trim()) || 'webhook';
        }
        if (!slug) slug = 'my-api-hook';
        createSlugPreview.textContent = slug;
    }
    if (createSlugFromName) createSlugFromName.addEventListener('change', function () { updateCreateSlugVisibility(); updateCreatePreview(); });
    if (createName) { createName.addEventListener('input', updateCreatePreview); createName.addEventListener('change', updateCreatePreview); }
    if (createSlug) createSlug.addEventListener('input', updateCreatePreview);
    updateCreateSlugVisibility();
    updateCreatePreview();

    <?php if ($createError): ?>
    openModal('create-modal');
    <?php endif; ?>
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
