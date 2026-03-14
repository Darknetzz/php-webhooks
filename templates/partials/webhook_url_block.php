<?php
// Expects $webhookUrl (full URL), optional $wrapTag = 'div'
$wrapTag = $wrapTag ?? 'div';
?>
<<?= $wrapTag ?> class="webhook-url-wrap">
    <span class="webhook-url"><?= e($webhookUrl) ?></span>
    <button type="button" class="btn-copy-webhook btn-webhook-action" title="Copy URL"><svg class="icon" aria-hidden="true"><use href="#icon-copy"/></svg> Copy</button>
    <button type="button" class="btn-open-webhook btn-webhook-action" title="Open in new tab" data-url="<?= e($webhookUrl) ?>"><svg class="icon" aria-hidden="true"><use href="#icon-external"/></svg> Open</button>
</<?php echo $wrapTag; ?>>
