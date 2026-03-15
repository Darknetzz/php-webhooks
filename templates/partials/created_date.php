<?php
// Expects $date (string). Optional $label (string): if non-empty, shown before date (e.g. "Created "). Outputs calendar icon + optional label + date.
$label = $label ?? 'Created ';
?>
<span class="created-date">
    <svg class="icon" aria-hidden="true"><use href="#icon-calendar"/></svg><?php if ($label !== ''): ?><?= e($label) ?><?php endif; ?><?= e($date) ?>
</span>
