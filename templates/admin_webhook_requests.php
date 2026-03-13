<?php
$title = 'Requests: ' . e($webhook->name);
$config = config();
$baseUrl = rtrim(base_url(), '/');
$webhookBaseUrl = rtrim(webhook_base_url(), '/');
ob_start();
?>
<h1>Requests: <?= e($webhook->name) ?></h1>
<p class="meta" style="margin-bottom: 1rem;">
    <a href="<?= e($baseUrl) ?>/admin/webhooks">← Webhooks</a>
    &nbsp;·&nbsp;
    <span class="webhook-url-wrap">
    <span class="webhook-url"><?= e($webhookBaseUrl) ?>/w/<?= e($webhook->slug) ?></span>
    <button type="button" class="btn-copy-webhook" title="Copy URL">Copy</button>
</span>
</p>

<?php if (empty($requests)): ?>
    <div class="empty-state">
        <p>No requests yet. Send a request to the webhook URL to see it here.</p>
        <p><code class="code">curl -X POST "<?= e($webhookBaseUrl) ?>/w/<?= e($webhook->slug) ?>" -d '{"test": true}'</code></p>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Method</th>
                    <th>IP</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?= e($r->created_at) ?></td>
                        <td><?= e($r->method) ?></td>
                        <td><?= e($r->ip ?? '-') ?></td>
                        <td>
                            <a href="#" class="btn btn-ghost" style="font-size: 0.85rem; padding: 0.25rem 0.5rem;" onclick="toggleDetail(<?= $r->id ?>); return false;">Details</a>
                        </td>
                    </tr>
                    <tr id="detail-<?= $r->id ?>" style="display: none;" class="detail-row">
                        <td colspan="4" class="request-detail">
                            <div class="meta">Headers (excerpt)</div>
                            <div class="request-body"><?= e(mb_substr($r->headers, 0, 500)) ?><?= mb_strlen($r->headers) > 500 ? '…' : '' ?></div>
                            <?php if ($r->query_string !== ''): ?>
                                <div class="meta" style="margin-top: 0.5rem;">Query string</div>
                                <div class="request-body"><?= e($r->query_string) ?></div>
                            <?php endif; ?>
                            <?php if ($r->body !== ''): ?>
                                <div class="meta" style="margin-top: 0.5rem;">Body</div>
                                <div class="request-body"><?= e(mb_substr($r->body, 0, 2000)) ?><?= mb_strlen($r->body) > 2000 ? '…' : '' ?></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
    function toggleDetail(id) {
        var el = document.getElementById('detail-' + id);
        el.style.display = el.style.display === 'none' ? 'table-row' : 'none';
    }
    </script>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
