<?php
// Expects $webhookUrl (full URL), optional $wrapTag = 'div'
$wrapTag = $wrapTag ?? 'div';
?>
<<?= $wrapTag ?> class="webhook-url-wrap">
    <span class="webhook-url"><?= e($webhookUrl) ?></span>
    <button type="button" class="btn-copy-webhook" title="Copy URL">Copy</button>
    <a href="<?= e($webhookUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-open-webhook" title="Open in new tab">Open</a>
</<?php echo $wrapTag; ?>>
