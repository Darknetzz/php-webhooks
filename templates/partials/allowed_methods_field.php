<?php
// $allowedMethodOptions: array of method names (e.g. from webhook_allowed_method_options())
// $selectedMethods: array of method names to pre-check (e.g. parse_allowed_methods($webhook->allowed_methods))
// $inputName: name for the checkboxes, e.g. 'allowed_methods[]'
$allowedMethodOptions = $allowedMethodOptions ?? webhook_allowed_method_options();
$selectedMethods = $selectedMethods ?? [];
$inputName = $inputName ?? 'allowed_methods[]';
?>
<div class="form-group">
    <span class="settings-label" style="display: block; margin-bottom: 0.35rem;">Allowed HTTP methods</span>
    <p class="hint" style="margin-bottom: 0.5rem;">Leave all unchecked to allow any method. Otherwise only selected methods are accepted.</p>
    <div class="allowed-methods-wrap" style="display: flex; flex-wrap: wrap; gap: 0.5rem 1rem;">
        <?php foreach ($allowedMethodOptions as $method): ?>
            <label class="checkbox-label" style="margin: 0;">
                <input type="checkbox" name="<?= e($inputName) ?>" value="<?= e($method) ?>" <?= in_array($method, $selectedMethods, true) ? 'checked' : '' ?>>
                <?= e($method) ?>
            </label>
        <?php endforeach; ?>
    </div>
</div>
