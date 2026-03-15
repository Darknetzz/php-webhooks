<?php
// Expects $isPublic (bool), optional $publicLabel (string, default 'Public'), optional $requestsPublic (bool).
// Outputs icon + label for listing visibility. Requests can only be public when listed; when private we show only "Private".
$publicLabel = $publicLabel ?? 'Public';
$requestsPublic = ($requestsPublic ?? false) && $isPublic;
if ($isPublic && $requestsPublic) {
    $visibilityText = $publicLabel . ' & requests public';
} elseif ($isPublic) {
    $visibilityText = $publicLabel;
} else {
    $visibilityText = 'Private';
}
?>
<span class="visibility-label">
<?php if ($isPublic): ?>
    <svg class="icon" aria-hidden="true"><use href="#icon-globe"/></svg>
<?php else: ?>
    <svg class="icon" aria-hidden="true"><use href="#icon-lock"/></svg>
<?php endif; ?>
<?= e($visibilityText) ?>
</span>
