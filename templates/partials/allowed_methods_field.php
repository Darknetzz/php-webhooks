<?php
// $allowedMethodOptions: array of method names (e.g. from webhook_allowed_method_options())
// $selectedMethods: array of method names to pre-check (e.g. parse_allowed_methods($webhook->allowed_methods))
// $inputName: name for the checkboxes, e.g. 'allowed_methods[]'
// $specifyToggleId: id for the "Specify allowed methods" checkbox (e.g. 'create-specify-allowed-methods')
$allowedMethodOptions = $allowedMethodOptions ?? webhook_allowed_method_options();
$selectedMethods = $selectedMethods ?? [];
$inputName = $inputName ?? 'allowed_methods[]';
$specifyToggleId = $specifyToggleId ?? 'specify-allowed-methods';
$specifyChecked = count($selectedMethods) > 0;
?>
<div class="allowed-methods-toggle-section form-group">
    <label class="checkbox-label" for="<?= e($specifyToggleId) ?>">
        <input type="checkbox" id="<?= e($specifyToggleId) ?>" class="specify-allowed-methods-toggle" <?= $specifyChecked ? 'checked' : '' ?>>
        Specify allowed methods
    </label>
    <div class="allowed-methods-inner" style="margin-top: 0.75rem; <?= $specifyChecked ? '' : 'display: none;' ?>">
        <p class="hint" style="margin-bottom: 0.5rem;">Only selected methods are accepted. When unspecified, any method is allowed.</p>
        <div class="allowed-methods-wrap" style="display: flex; flex-wrap: wrap; gap: 0.5rem 1rem;">
            <?php foreach ($allowedMethodOptions as $method): ?>
                <label class="checkbox-label" style="margin: 0;">
                    <input type="checkbox" name="<?= e($inputName) ?>" value="<?= e($method) ?>" <?= in_array($method, $selectedMethods, true) ? 'checked' : '' ?> <?= $specifyChecked ? '' : 'disabled' ?>>
                    <?= e($method) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
</div>
