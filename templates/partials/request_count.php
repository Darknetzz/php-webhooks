<?php
// Expects $count (int). Optional $url (string): if set, wrap in a link; otherwise plain span.
$count = (int) $count;
$label = $count === 1 ? 'request' : 'requests';
$inner = '<svg class="icon" aria-hidden="true"><use href="#icon-requests"/></svg><strong>' . $count . '</strong> ' . $label;
?>
<?php if (!empty($url)): ?>
<a href="<?= e($url) ?>" class="request-count request-count--link"><?= $inner ?></a>
<?php else: ?>
<span class="request-count"><?= $inner ?></span>
<?php endif; ?>
