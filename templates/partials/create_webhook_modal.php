<?php
// Create webhook modal. Expects $baseUrl, $webhookBaseUrl; optional $createError, $createName, $createSlug, etc.
// Set $fromAdmin = true when including from admin all-webhooks page (adds hidden input, server redirects back to admin).
$createError = $createError ?? null;
$createName = $createName ?? '';
$createSlug = $createSlug ?? '';
$createDescription = $createDescription ?? '';
$createIsPublic = isset($createIsPublic) ? $createIsPublic : false;
$createRequestsPublic = $createRequestsPublic ?? false;
$createSlugFromName = $createSlugFromName ?? true;
$createResponseStatusCode = (int) ($createResponseStatusCode ?? 200);
$createResponseHeaders = $createResponseHeaders ?? '';
$createResponseBody = $createResponseBody ?? '';
$createAllowedMethods = $createAllowedMethods ?? [];
$allowedMethodOptions = $allowedMethodOptions ?? webhook_allowed_method_options();
$fromAdmin = $fromAdmin ?? false;
?>
<!-- Create webhook modal -->
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
                <?php if ($fromAdmin): ?><input type="hidden" name="from_admin" value="1"><?php endif; ?>
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
                        <div id="create-custom-slug-input-wrap">
                            <label for="create-slug">Custom slug (optional)</label>
                            <input type="text" id="create-slug" name="slug" placeholder="my-api-hook" pattern="[a-zA-Z0-9_-]+" value="<?= e($createSlug) ?>">
                        </div>
                        <div class="hint">URL: <strong><?= e($webhookBaseUrl) ?>/w/<span id="create-slug-preview">my-api-hook</span></strong></div>
                    </div>
                    <div class="form-group" id="create-random-slug-hint" style="display: none;">
                        <input type="hidden" name="slug_random" id="create-slug-random" value="">
                        <div class="hint">URL: <strong><?= e($webhookBaseUrl) ?>/w/<span id="create-random-slug-url-preview"></span></strong></div>
                    </div>
                </div>
                <div class="form-section">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_public" value="1" <?= $createIsPublic ? 'checked' : '' ?>>
                            List on public page
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
                    <h3 class="form-section-title">Allowed methods</h3>
                    <?php $selectedMethods = $createAllowedMethods; $specifyToggleId = 'create-specify-allowed-methods'; require __DIR__ . '/allowed_methods_field.php'; ?>
                </div>
                <div class="form-section">
                    <h3 class="form-section-title">Response (optional)</h3>
                    <?php require __DIR__ . '/response_variables_hint.php'; ?>
                    <div class="form-group">
                        <label for="create-response_status_code">Status code</label>
                        <input type="number" id="create-response_status_code" name="response_status_code" min="100" max="599" value="<?= $createResponseStatusCode ?>">
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
<script>
(function () {
    var modal = document.getElementById('create-modal');
    if (!modal) return;
    function openCreateModal() {
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function closeCreateModal() {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('.btn-add-webhook')) {
            e.preventDefault();
            openCreateModal();
        }
    });
    modal.querySelectorAll('[data-close="create-modal"]').forEach(function (btn) {
        btn.addEventListener('click', closeCreateModal);
    });
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeCreateModal();
    });

    var createSlugFromName = document.getElementById('create-slug_from_name');
    var createName = document.getElementById('create-name');
    var createSlug = document.getElementById('create-slug');
    var createSlugPreview = document.getElementById('create-slug-preview');
    var createSlugFieldWrap = document.getElementById('create-slug-field-wrap');
    var createCustomSlugInputWrap = document.getElementById('create-custom-slug-input-wrap');
    var createRandomSlugHint = document.getElementById('create-random-slug-hint');
    var createRandomSlugUrlPreview = document.getElementById('create-random-slug-url-preview');
    var createSlugRandomInput = document.getElementById('create-slug-random');
    if (createSlugPreview) {
        var slugify = function (s) {
            return s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || '';
        };
        function randomHexSample(minLen, maxLen) {
            minLen = minLen || 10;
            maxLen = maxLen || 30;
            var len = minLen + Math.floor(Math.random() * (maxLen - minLen + 1));
            var hex = '0123456789abcdef';
            var out = '';
            for (var i = 0; i < len; i++) out += hex[Math.floor(Math.random() * 16)];
            return out;
        }
        function updateCreateSlugVisibility() {
            var fromName = createSlugFromName && createSlugFromName.checked;
            if (createSlugFieldWrap) createSlugFieldWrap.style.display = fromName ? 'block' : 'none';
            if (createCustomSlugInputWrap) createCustomSlugInputWrap.style.display = fromName ? 'none' : 'block';
            if (createRandomSlugHint) createRandomSlugHint.style.display = fromName ? 'none' : 'block';
            if (!fromName && createSlug) createSlug.value = '';
            if (!fromName) {
                var sample = randomHexSample(10, 30);
                if (createSlugRandomInput) createSlugRandomInput.value = sample;
                if (createRandomSlugUrlPreview) createRandomSlugUrlPreview.textContent = sample;
            }
        }
        function updateCreatePreview() {
            var fromName = createSlugFromName && createSlugFromName.checked;
            var slug = (createSlug && createSlug.value.trim()) || '';
            if (!fromName && !slug) {
                var sample = createSlugRandomInput ? createSlugRandomInput.value : randomHexSample(10, 30);
                createSlugPreview.textContent = sample || '(random)';
                return;
            }
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
    }
    <?php if ($createError): ?>
    openCreateModal();
    <?php endif; ?>
})();
</script>
