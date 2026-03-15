<?php
// Reusable edit webhook modal. Expects $baseUrl, $webhookBaseUrl in scope.
// Include edit_webhook_modal_script.php after this to bind .btn-edit-webhook to open modal with data from .webhook-card or .webhook-row.
?>
<div class="modal-overlay" id="edit-modal" role="dialog" aria-modal="true" aria-labelledby="edit-modal-title">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2 id="edit-modal-title">Edit webhook</h2>
            <button type="button" class="modal-close" data-close="edit-modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="post" id="edit-webhook-form" action="">
                <div class="form-group">
                    <label for="edit-name">Name</label>
                    <input type="text" id="edit-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit-slug">Slug (URL path)</label>
                    <input type="text" id="edit-slug" name="slug" pattern="[a-zA-Z0-9_-]+">
                    <div class="hint"><?= e($webhookBaseUrl ?? '') ?>/w/<strong id="edit-slug-preview"></strong></div>
                </div>
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <textarea id="edit-description" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_public" value="1" id="edit-is_public">
                        List on public page
                    </label>
                </div>
                <div class="form-group js-requests-public-wrap">
                    <label class="checkbox-label">
                        <input type="checkbox" name="requests_public" value="1" id="edit-requests_public">
                        Show requests publicly
                    </label>
                </div>
                <div class="form-section">
                    <h3 class="form-section-title">Allowed methods</h3>
                    <?php $selectedMethods = []; $specifyToggleId = 'edit-specify-allowed-methods'; require __DIR__ . '/allowed_methods_field.php'; ?>
                </div>
                <div class="form-section">
                    <h3 class="form-section-title">Response (optional)</h3>
                    <?php require __DIR__ . '/response_variables_hint.php'; ?>
                    <div class="form-group">
                        <label for="edit-response_status_code">Status code</label>
                        <input type="number" id="edit-response_status_code" name="response_status_code" min="100" max="599">
                    </div>
                    <?php $prefix = 'edit-'; $responseHeadersValue = ''; $responseBodyValue = ''; require __DIR__ . '/response_headers_body_fields.php'; ?>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-ghost" data-close="edit-modal">Cancel</button>
            </form>
        </div>
    </div>
</div>
