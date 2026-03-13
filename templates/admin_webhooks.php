<?php
$title = 'Create Webhook';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$webhookBaseUrl = rtrim(webhook_base_url(), '/');
$createError = $createError ?? null;
$createName = $createName ?? '';
$createSlug = $createSlug ?? '';
$createDescription = $createDescription ?? '';
$createIsPublic = isset($createIsPublic) ? $createIsPublic : true;
ob_start();
?>
<h1>Create Webhook</h1>
<?php if ($createError): ?>
    <div class="error-msg"><?= e($createError) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.1rem; margin-bottom: 0.75rem;">Create webhook</h2>
    <form method="post" action="">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required placeholder="My API hook" value="<?= e($createName) ?>">
        </div>
        <div class="form-group">
            <label for="slug">Slug (URL path)</label>
            <input type="text" id="slug" name="slug" placeholder="my-api-hook" pattern="[a-zA-Z0-9_-]+" title="Letters, numbers, underscore, hyphen only" value="<?= e($createSlug) ?>">
            <div class="hint">Used in URL. Leave empty to generate from name.</div>
            <div class="hint" id="slug-preview-wrap" style="margin-top: 0.25rem;">URL will be: <strong><?= e($webhookBaseUrl) ?>/w/<span id="slug-preview">my-api-hook</span></strong></div>
        </div>
        <div class="form-group">
            <label for="description">Description (optional)</label>
            <textarea id="description" name="description" placeholder="What this webhook is for"><?= e($createDescription) ?></textarea>
        </div>
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_public" value="1" <?= $createIsPublic ? 'checked' : '' ?>>
                List on public page (anyone can see the URL)
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
<script>
(function () {
    var nameEl = document.getElementById('name');
    var slugEl = document.getElementById('slug');
    var previewEl = document.getElementById('slug-preview');
    function slugify(s) {
        return s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || '';
    }
    function updatePreview() {
        var slug = (slugEl && slugEl.value.trim()) || '';
        if (!slug && nameEl && nameEl.value.trim()) {
            slug = slugify(nameEl.value.trim()) || 'webhook-' + Math.floor(Date.now() / 1000);
        }
        if (!slug) slug = 'my-api-hook';
        if (previewEl) previewEl.textContent = slug;
    }
    if (nameEl) nameEl.addEventListener('input', updatePreview);
    if (nameEl) nameEl.addEventListener('change', updatePreview);
    if (slugEl) slugEl.addEventListener('input', updatePreview);
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
            <div class="webhook-url"><?= e($webhookBaseUrl) ?>/w/<?= e($w->slug) ?></div>
            <p class="meta"><?= $w->is_public ? 'Public' : 'Private' ?> · Created <?= e($w->created_at) ?></p>
            <div class="card-actions">
                <a href="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/requests" class="btn btn-ghost">View requests</a>
                <a href="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/edit" class="btn btn-ghost">Edit</a>
                <form method="post" action="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/delete" style="display: inline;" onsubmit="return confirm('Delete this webhook and all its request history?');">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
