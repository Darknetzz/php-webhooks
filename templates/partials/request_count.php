<?php
// Expects $count (int). Outputs icon + bold count + "request" or "requests".
?>
<span class="request-count">
    <svg class="icon" aria-hidden="true"><use href="#icon-requests"/></svg><strong><?= (int) $count ?></strong> request<?= (int) $count === 1 ? '' : 's' ?>
</span>
