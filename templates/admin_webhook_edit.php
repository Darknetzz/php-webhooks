<?php
$title = 'Edit webhook';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$webhookBaseUrl = rtrim(webhook_base_url(), '/');
ob_start();
?>
<h1>Edit webhook</h1>
<div class="card" style="max-width: 500px;">
    <form method="post" action="">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required value="<?= e($webhook->name) ?>">
        </div>
        <div class="form-group">
            <label for="slug">Slug (URL path)</label>
            <input type="text" id="slug" name="slug" value="<?= e($webhook->slug) ?>" pattern="[a-zA-Z0-9_-]+">
            <div class="hint"><?= e($webhookBaseUrl) ?>/w/<strong id="slug-preview"><?= e($webhook->slug) ?></strong></div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= e($webhook->description) ?></textarea>
        </div>
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_public" value="1" <?= $webhook->is_public ? 'checked' : '' ?>>
                List on public page
            </label>
        </div>
        <div class="form-group js-requests-public-wrap">
            <label class="checkbox-label">
                <input type="checkbox" name="requests_public" value="1" <?= $webhook->requests_public ? 'checked' : '' ?>>
                Show requests publicly
            </label>
        </div>
        <div class="form-section" style="margin-top: 1.25rem;">
            <h3 class="form-section-title">Allowed methods</h3>
            <?php $selectedMethods = parse_allowed_methods($webhook->allowed_methods ?? ''); $specifyToggleId = 'specify-allowed-methods'; require __DIR__ . '/partials/allowed_methods_field.php'; ?>
        </div>
        <div class="form-section" style="margin-top: 1.25rem;">
            <h3 class="form-section-title">Response (optional)</h3>
            <p class="hint" style="margin-bottom: 0.75rem;">Customize the HTTP response when the webhook is called. Empty = default.</p>
            <?php require __DIR__ . '/partials/response_variables_hint.php'; ?>
            <div class="form-group">
                <label for="response_status_code">Status code</label>
                <input type="number" id="response_status_code" name="response_status_code" min="100" max="599" value="<?= (int) $webhook->response_status_code ?>" placeholder="200">
            </div>
            <div class="form-group">
                <label for="response_headers">Response headers (JSON)</label>
                <textarea id="response_headers" name="response_headers" rows="3" placeholder='{"Content-Type": "application/json"}'><?= e($webhook->response_headers) ?></textarea>
            </div>
            <div class="form-group">
                <label for="response_body">Response body</label>
                <textarea id="response_body" name="response_body" rows="4" placeholder="Leave empty for default"><?= e($webhook->response_body) ?></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="<?= e($baseUrl) ?>/admin/webhooks" class="btn btn-ghost">Cancel</a>
    </form>
</div>
<script>
document.getElementById('slug').addEventListener('input', function() {
    document.getElementById('slug-preview').textContent = this.value || '<?= e($webhook->slug) ?>';
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
