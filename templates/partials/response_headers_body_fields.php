<?php
// Reusable response headers and body fields with Preset/Key-Value + Manual modes (like webhook test form).
// Expects $prefix (e.g. 'create-', 'edit-', or '' for standalone edit page).
// Optional $responseHeadersValue, $responseBodyValue for initial textarea content.
$prefix = $prefix ?? '';
$responseHeadersValue = $responseHeadersValue ?? '';
$responseBodyValue = $responseBodyValue ?? '';
$headersId = $prefix ? $prefix . 'response_headers' : 'response_headers';
$bodyId = $prefix ? $prefix . 'response_body' : 'response_body';
?>
<div class="webhook-form-response-section" data-prefix="<?= e($prefix) ?>">
    <div class="form-group webhook-form-response-headers-group test-webhook-headers-group" data-prefix="<?= e($prefix) ?>">
        <label for="<?= e($headersId) ?>">Response headers (JSON)</label>
        <div class="test-webhook-mode" role="tablist" aria-label="Response headers input mode">
            <button type="button" class="test-webhook-mode-btn is-active" data-mode="preset" aria-selected="true">Preset</button>
            <button type="button" class="test-webhook-mode-btn" data-mode="manual" aria-selected="false">Manual</button>
        </div>
        <div class="webhook-form-headers-preset test-webhook-preset-block" id="<?= e($prefix) ?>response-headers-preset">
            <div class="table-wrap">
                <table class="test-webhook-kv-table" id="<?= e($prefix) ?>response-headers-table">
                    <thead>
                        <tr><th>Header</th><th>Value</th><th class="table-cell-actions"></th></tr>
                    </thead>
                    <tbody id="<?= e($prefix) ?>response-headers-tbody"></tbody>
                </table>
            </div>
            <button type="button" class="test-webhook-add-row btn btn-ghost btn-sm webhook-form-add-header" data-prefix="<?= e($prefix) ?>" aria-label="Add header">+ Add header</button>
        </div>
        <div class="webhook-form-headers-manual test-webhook-manual-block" id="<?= e($prefix) ?>response-headers-manual" hidden>
            <textarea id="<?= e($headersId) ?>" name="response_headers" rows="3" placeholder='{"Content-Type": "application/json"}'><?= e($responseHeadersValue) ?></textarea>
        </div>
    </div>
    <div class="form-group webhook-form-response-body-group test-webhook-body-group" data-prefix="<?= e($prefix) ?>">
        <label for="<?= e($bodyId) ?>">Response body</label>
        <div class="test-webhook-mode" role="tablist" aria-label="Response body input mode">
            <button type="button" class="test-webhook-mode-btn is-active" data-mode="kv" aria-selected="true">Key/Value</button>
            <button type="button" class="test-webhook-mode-btn" data-mode="manual" aria-selected="false">Manual</button>
        </div>
        <div class="webhook-form-body-kv test-webhook-preset-block" id="<?= e($prefix) ?>response-body-kv">
            <div class="table-wrap">
                <table class="test-webhook-kv-table" id="<?= e($prefix) ?>response-body-table">
                    <thead>
                        <tr><th>Key</th><th>Value</th><th class="table-cell-actions"></th></tr>
                    </thead>
                    <tbody id="<?= e($prefix) ?>response-body-tbody"></tbody>
                </table>
            </div>
            <button type="button" class="test-webhook-add-row btn btn-ghost btn-sm webhook-form-add-body-row" data-prefix="<?= e($prefix) ?>" aria-label="Add row">+ Add row</button>
        </div>
        <div class="webhook-form-body-manual test-webhook-manual-block" id="<?= e($prefix) ?>response-body-manual" hidden>
            <textarea id="<?= e($bodyId) ?>" name="response_body" rows="4" placeholder="Leave empty for default"><?= e($responseBodyValue) ?></textarea>
        </div>
    </div>
</div>
