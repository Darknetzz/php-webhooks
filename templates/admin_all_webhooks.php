<?php
$title = 'All Webhooks';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$webhookBaseUrl = rtrim(webhook_base_url(), '/');
$adminActive = 'webhooks';
$fromAdmin = true;
$createError = null;
$createName = '';
$createSlug = '';
$createDescription = '';
$createIsPublic = true;
$createSlugFromName = true;
$createResponseStatusCode = 200;
$createResponseHeaders = '';
$createResponseBody = '';
$createAllowedMethods = [];
ob_start();
?>
<div class="page-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
    <h1 style="margin: 0;">All Webhooks</h1>
    <button type="button" class="btn btn-primary btn-add-webhook" id="btn-add-webhook" aria-controls="create-modal"><svg class="icon" aria-hidden="true"><use href="#icon-plus"/></svg> Add webhook</button>
</div>
<?php require __DIR__ . '/partials/admin_nav_pills.php'; ?>

<?php require __DIR__ . '/partials/create_webhook_modal.php'; ?>

<?php if (empty($webhooksWithOwners)): ?>
    <div class="empty-state">
        <p>No webhooks exist yet.</p>
        <button type="button" class="btn btn-primary btn-add-webhook"><svg class="icon" aria-hidden="true"><use href="#icon-plus"/></svg> Add webhook</button>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug / URL</th>
                    <th>Owner</th>
                    <th>Visibility</th>
                    <th>Created</th>
                    <th class="table-cell-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($webhooksWithOwners as $item): $w = $item['webhook']; $owner = $item['owner_username']; ?>
                    <tr>
                        <td><?= e($w->name) ?></td>
                        <td>
                            <?php $webhookUrl = $webhookBaseUrl . '/w/' . $w->slug; $iconOnly = true; require __DIR__ . '/partials/webhook_url_block.php'; ?>
                        </td>
                        <td><?= e($owner) ?></td>
                        <td><?= $w->is_public ? 'Public' : 'Private' ?></td>
                        <td><?= e($w->created_at) ?></td>
                        <td class="table-cell-actions card-actions">
                            <a href="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/requests" class="btn btn-ghost" style="padding: 0.35rem 0.6rem; font-size: 0.85rem;">Requests</a>
                            <a href="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/edit" class="btn btn-ghost" style="padding: 0.35rem 0.6rem; font-size: 0.85rem;">Edit</a>
                            <form method="post" action="<?= e($baseUrl) ?>/admin/webhooks/<?= $w->id ?>/delete" style="display: inline;" onsubmit="return confirm('Delete this webhook and all its request history?');">
                                <button type="submit" class="btn btn-danger" style="padding: 0.35rem 0.6rem; font-size: 0.85rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
