<?php
$title = 'My Webhooks';
$config = config();
$baseUrl = rtrim($config['url'], '/');
$createError = $createError ?? null;
ob_start();
?>
<h1>My Webhooks</h1>
<?php if ($createError): ?>
    <div class="error-msg"><?= e($createError) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.1rem; margin-bottom: 0.75rem;">Create webhook</h2>
    <form method="post" action="">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required placeholder="My API hook">
        </div>
        <div class="form-group">
            <label for="slug">Slug (URL path)</label>
            <input type="text" id="slug" name="slug" placeholder="my-api-hook" pattern="[a-zA-Z0-9_-]+" title="Letters, numbers, underscore, hyphen only">
            <div class="hint">Used in URL: <?= e($baseUrl) ?>/w/<strong>slug</strong>. Leave empty to auto-generate.</div>
        </div>
        <div class="form-group">
            <label for="description">Description (optional)</label>
            <textarea id="description" name="description" placeholder="What this webhook is for"></textarea>
        </div>
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_public" value="1" checked>
                List on public page (anyone can see the URL)
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>

<?php if (empty($webhooks)): ?>
    <div class="empty-state">
        <p>No webhooks yet. Create one above.</p>
    </div>
<?php else: ?>
    <?php foreach ($webhooks as $w): ?>
        <div class="card">
            <h3><?= e($w->name) ?></h3>
            <?php if ($w->description): ?>
                <p class="meta"><?= e($w->description) ?></p>
            <?php endif; ?>
            <div class="webhook-url"><?= e($baseUrl) ?>/w/<?= e($w->slug) ?></div>
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
