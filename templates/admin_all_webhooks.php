<?php
$title = 'All Webhooks';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$webhookBaseUrl = rtrim(webhook_base_url(), '/');
$adminActive = 'webhooks';
ob_start();
?>
<h1>All Webhooks</h1>
<?php require __DIR__ . '/partials/admin_nav_pills.php'; ?>
<p class="meta" style="margin-bottom: 1rem;"><a href="<?= e($baseUrl) ?>/">Webhooks</a></p>
<?php if (empty($webhooksWithOwners)): ?>
    <div class="empty-state">
        <p>No webhooks exist yet.</p>
        <a href="<?= e($baseUrl) ?>/" class="btn btn-primary">Create webhook</a>
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
                            <?php $webhookUrl = $webhookBaseUrl . '/w/' . $w->slug; require __DIR__ . '/partials/webhook_url_block.php'; ?>
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
