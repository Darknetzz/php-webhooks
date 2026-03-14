<?php
$title = 'Requests: ' . e($webhook->name);
$config = config();
$baseUrl = rtrim(base_url(), '/');
$webhookBaseUrl = rtrim(webhook_base_url(), '/');
ob_start();
?>
<div class="page-header" style="margin-bottom: 1rem;">
    <h1 style="margin: 0;">Requests: <?= e($webhook->name) ?></h1>
</div>
<p class="meta requests-url-line" style="margin-bottom: 1rem;">
    <a href="<?= e($baseUrl) ?>/">← Webhooks</a>
    <span aria-hidden="true">·</span>
    <?php $webhookUrl = $webhookBaseUrl . '/w/' . $webhook->slug; $wrapTag = 'span'; require __DIR__ . '/partials/webhook_url_block.php'; ?>
    <a href="<?= e($baseUrl) ?>/admin/webhooks/<?= (int) $webhook->id ?>/requests" class="btn-webhook-action" title="Refresh list"><svg class="icon" aria-hidden="true"><use href="#icon-refresh"/></svg> Refresh</a>
    <?php if (!empty($requests)): ?>
    <form method="post" action="<?= e($baseUrl) ?>/admin/webhooks/<?= (int) $webhook->id ?>/requests/delete-all" style="display: inline;" onsubmit="return confirm('Delete all <?= count($requests) ?> request(s) for this webhook?');">
        <button type="submit" class="btn-webhook-action btn-danger-inline" title="Delete all requests"><svg class="icon" aria-hidden="true"><use href="#icon-trash"/></svg> Delete all</button>
    </form>
    <?php endif; ?>
</p>

<?php if (empty($requests)): ?>
    <div class="empty-state">
        <p>No requests yet. Send a request to the webhook URL to see it here.</p>
        <?php $code = 'curl -X POST "' . $webhookBaseUrl . '/w/' . $webhook->slug . '" -d \'{"test": true}\''; $language = 'bash'; require __DIR__ . '/partials/codebox.php'; ?>
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
                            <form method="post" action="<?= e($baseUrl) ?>/admin/webhooks/<?= (int) $webhook->id ?>/requests/<?= (int) $r->id ?>/delete" style="display: inline;" onsubmit="return confirm('Delete this request?');">
                                <button type="submit" class="btn btn-ghost btn-danger-inline" style="font-size: 0.85rem; padding: 0.25rem 0.5rem;" title="Delete request"><svg class="icon" aria-hidden="true"><use href="#icon-trash"/></svg></button>
                            </form>
                        </td>
                    </tr>
                    <tr id="detail-<?= $r->id ?>" style="display: none;" class="detail-row">
                        <td colspan="4" class="request-detail">
                            <div class="meta">Headers</div>
                            <pre class="request-body"><code class="json-beautify"><?= e($r->headers) ?></code></pre>
                            <?php if ($r->query_string !== ''): ?>
                                <div class="meta" style="margin-top: 0.75rem;">Query string</div>
                                <pre class="request-body"><code><?= e($r->query_string) ?></code></pre>
                            <?php endif; ?>
                            <?php if ($r->body !== ''): ?>
                                <div class="meta" style="margin-top: 0.75rem;">Body</div>
                                <pre class="request-body"><code class="json-beautify"><?= e($r->body) ?></code></pre>
                            <?php else: ?>
                                <div class="meta" style="margin-top: 0.75rem;">Body</div>
                                <pre class="request-body"><code class="meta">(empty)</code></pre>
                            <?php endif; ?>
                            <?php if ($r->response_status_code !== null || $r->response_headers !== '' || $r->response_body !== ''): ?>
                                <div class="meta" style="margin-top: 1rem;">Response</div>
                                <?php if ($r->response_status_code !== null): ?>
                                    <div class="meta" style="margin-top: 0.35rem;">Status: <strong><?= (int) $r->response_status_code ?></strong></div>
                                <?php endif; ?>
                                <?php if ($r->response_headers !== ''): ?>
                                    <div class="meta" style="margin-top: 0.35rem;">Response headers</div>
                                    <pre class="request-body"><code class="json-beautify"><?= e($r->response_headers) ?></code></pre>
                                <?php endif; ?>
                                <?php if ($r->response_body !== ''): ?>
                                    <div class="meta" style="margin-top: 0.35rem;">Response body</div>
                                    <pre class="request-body"><code class="json-beautify"><?= e($r->response_body) ?></code></pre>
                                <?php endif; ?>
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
