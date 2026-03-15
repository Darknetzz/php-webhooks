<?php
// Expects $isPublic (bool), optional $publicLabel (string, default 'Public'). Outputs icon + label for listing visibility.
$publicLabel = $publicLabel ?? 'Public';
?>
<span class="visibility-label">
<?php if ($isPublic): ?>
    <svg class="icon" aria-hidden="true"><use href="#icon-globe"/></svg><?= e($publicLabel) ?>
<?php else: ?>
    <svg class="icon" aria-hidden="true"><use href="#icon-lock"/></svg>Private
<?php endif; ?>
</span>
