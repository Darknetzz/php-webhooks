<?php
$title = 'Create Webhook';
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
ob_start();
?>
<h1>Create Webhook</h1>
<?php if ($createError): ?>
    <div class="error-msg"><?= e($createError) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.1rem; margin-bottom: 0.75rem;">Create webhook</h2>
    <form method="post" action="" id="create-webhook-form">
        <div class="form-section">
            <h3 class="form-section-title">Basic</h3>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required placeholder="My API hook" value="<?= e($createName) ?>">
            </div>
            <div class="form-group">
                <label for="description">Description (optional)</label>
                <textarea id="description" name="description" placeholder="What this webhook is for"><?= e($createDescription) ?></textarea>
            </div>
        </div>
        <div class="form-section">
            <h3 class="form-section-title">URL</h3>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="slug_from_name" id="slug_from_name" value="1" <?= $createSlugFromName ? 'checked' : '' ?>>
                    Create slug from name
                </label>
                <div class="hint">When checked, the URL path is derived from the name if you leave the slug empty. When unchecked, a random slug (10–30 characters) is generated.</div>
            </div>
            <div class="form-group" id="slug-field-wrap">
                <label for="slug">Custom slug (optional)</label>
                <input type="text" id="slug" name="slug" placeholder="my-api-hook" pattern="[a-zA-Z0-9_-]+" title="Letters, numbers, underscore, hyphen only" value="<?= e($createSlug) ?>">
                <div class="hint">Letters, numbers, underscore, hyphen only. Overrides “Create slug from name” when set.</div>
                <div class="hint" id="slug-preview-wrap" style="margin-top: 0.25rem;">URL will be: <strong><?= e($webhookBaseUrl) ?>/w/<span id="slug-preview">my-api-hook</span></strong></div>
            </div>
            <div class="form-group" id="random-slug-hint" style="display: none;">
                <input type="hidden" name="slug_random" id="slug-random" value="">
                <div class="hint">URL: <strong><?= e($webhookBaseUrl) ?>/w/<span id="random-slug-url-preview"></span></strong></div>
            </div>
        </div>
        <div class="form-section">
            <h3 class="form-section-title">Visibility</h3>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_public" value="1" <?= $createIsPublic ? 'checked' : '' ?>>
                    List on public page (anyone can see the URL)
                </label>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="requests_public" value="1" <?= $createRequestsPublic ? 'checked' : '' ?>>
                    Show requests publicly
                </label>
            </div>
        </div>
        <div class="form-section">
            <h3 class="form-section-title">Response (optional)</h3>
            <p class="hint" style="margin-bottom: 0.75rem;">Customize the HTTP response returned when the webhook is called. Leave empty for default: <code>200</code> and <code>{"ok":true,"received":true}</code>.</p>
            <?php require __DIR__ . '/partials/response_variables_hint.php'; ?>
            <div class="form-group">
                <label for="response_status_code">Status code</label>
                <input type="number" id="response_status_code" name="response_status_code" min="100" max="599" value="<?= (int) $createResponseStatusCode ?>" placeholder="200">
            </div>
            <div class="form-group">
                <label for="response_headers">Response headers (JSON)</label>
                <textarea id="response_headers" name="response_headers" rows="3" placeholder='{"Content-Type": "application/json", "X-Custom": "value"}'><?= e($createResponseHeaders) ?></textarea>
                <div class="hint">JSON object of header name → value. E.g. <code>Content-Type</code>, <code>X-Request-Id</code>.</div>
            </div>
            <div class="form-group">
                <label for="response_body">Response body</label>
                <textarea id="response_body" name="response_body" rows="4" placeholder='Leave empty for default JSON'><?= e($createResponseBody) ?></textarea>
                <div class="hint">Raw body sent to the client. Empty = default <code>{"ok":true,"received":true}</code>. Can be JSON, XML, plain text, etc.</div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
<script>
(function () {
    var form = document.getElementById('create-webhook-form');
    var nameEl = document.getElementById('name');
    var slugEl = document.getElementById('slug');
    var slugFromNameEl = document.getElementById('slug_from_name');
    var slugFieldWrap = document.getElementById('slug-field-wrap');
    var randomSlugHint = document.getElementById('random-slug-hint');
    var previewEl = document.getElementById('slug-preview');
    var slugRandomInput = document.getElementById('slug-random');
    var randomSlugUrlPreview = document.getElementById('random-slug-url-preview');
    function slugify(s) {
        return s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || '';
    }
    function randomHexSample(minLen, maxLen) {
        minLen = minLen || 10;
        maxLen = maxLen || 30;
        var len = minLen + Math.floor(Math.random() * (maxLen - minLen + 1));
        var hex = '0123456789abcdef';
        var out = '';
        for (var i = 0; i < len; i++) out += hex[Math.floor(Math.random() * 16)];
        return out;
    }
    function updateSlugVisibility() {
        var fromName = slugFromNameEl && slugFromNameEl.checked;
        if (slugFieldWrap) slugFieldWrap.style.display = fromName ? 'block' : 'none';
        if (randomSlugHint) randomSlugHint.style.display = fromName ? 'none' : 'block';
        if (!fromName) {
            var sample = randomHexSample(10, 30);
            if (slugRandomInput) slugRandomInput.value = sample;
            if (randomSlugUrlPreview) randomSlugUrlPreview.textContent = sample;
        }
        if (!fromName && slugEl) slugEl.value = '';
    }
    function updatePreview() {
        if (!previewEl) return;
        var fromName = slugFromNameEl && slugFromNameEl.checked;
        var slug = (slugEl && slugEl.value.trim()) || '';
        if (!fromName && !slug) {
            var sample = slugRandomInput ? slugRandomInput.value : randomHexSample(10, 30);
            previewEl.textContent = sample || '(random)';
            return;
        }
        if (!slug && nameEl && nameEl.value.trim()) {
            slug = slugify(nameEl.value.trim()) || 'webhook-' + Math.floor(Date.now() / 1000);
        }
        if (!slug) slug = 'my-api-hook';
        previewEl.textContent = slug;
    }
    if (slugFromNameEl) slugFromNameEl.addEventListener('change', function() { updateSlugVisibility(); updatePreview(); });
    if (nameEl) { nameEl.addEventListener('input', updatePreview); nameEl.addEventListener('change', updatePreview); }
    if (slugEl) slugEl.addEventListener('input', updatePreview);
    updateSlugVisibility();
    updatePreview();
})();
</script>

<?php if (empty($webhooks)): ?>
    <div class="empty-state">
        <p>No webhooks yet. Use the form above to create one.</p>
    </div>
<?php else: ?>
    <h2 style="font-size: 1.1rem; margin: 1.5rem 0 0.75rem;">Your webhooks</h2>
    <?php foreach ($webhooks as $w): ?>
        <div class="card">
            <h3><?= e($w->name) ?></h3>
            <?php if ($w->description): ?>
                <p class="meta"><?= e($w->description) ?></p>
            <?php endif; ?>
            <?php $webhookUrl = $webhookBaseUrl . '/w/' . $w->slug; $iconOnly = true; require __DIR__ . '/partials/webhook_url_block.php'; ?>
            <p class="meta"><?php $isPublic = (bool) $w->is_public; $publicLabel = 'Public'; require __DIR__ . '/partials/visibility_label.php'; ?> · <?php $date = $w->created_at; $label = 'Created '; require __DIR__ . '/partials/created_date.php'; ?></p>
            <div class="card-actions">
                <a href="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/requests" class="btn btn-ghost">View requests</a>
                <a href="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/edit" class="btn btn-ghost btn-icon-only" aria-label="Edit"><svg class="icon" aria-hidden="true"><use href="#icon-edit"/></svg></a>
                <form method="post" action="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/delete" style="display: inline;" onsubmit="return confirm('Delete this webhook and all its request history?');">
                    <button type="submit" class="btn btn-danger btn-icon-only" aria-label="Delete"><svg class="icon" aria-hidden="true"><use href="#icon-trash"/></svg></button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
