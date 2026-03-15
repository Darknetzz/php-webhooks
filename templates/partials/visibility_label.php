<?php
// Expects $isPublic (bool), optional $publicLabel (string, default 'Public'), optional $requestsPublic (bool).
// Outputs icon + label for listing visibility; when $requestsPublic is true, combines or appends "Requests public".
$publicLabel = $publicLabel ?? 'Public';
$requestsPublic = $requestsPublic ?? false;
if ($isPublic && $requestsPublic) {
    $visibilityText = $publicLabel . ' & requests public';
} elseif ($isPublic) {
    $visibilityText = $publicLabel;
} elseif ($requestsPublic) {
    $visibilityText = 'Private · Requests public';
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
