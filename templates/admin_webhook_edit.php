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
