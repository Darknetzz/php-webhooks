<?php
// Expects $webhookUrl (full URL), optional $wrapTag = 'div', optional $iconOnly = false
$wrapTag = $wrapTag ?? 'div';
$iconOnly = $iconOnly ?? false;
?>
<<?= $wrapTag ?> class="webhook-url-wrap<?= $iconOnly ? ' webhook-url-wrap--icon-only' : '' ?>">
    <span class="webhook-url"><?= e($webhookUrl) ?></span>
    <button type="button" class="btn-copy-webhook btn-webhook-action" title="Copy URL" aria-label="Copy URL"><svg class="icon" aria-hidden="true"><use href="#icon-copy"/></svg><?php if (!$iconOnly): ?> Copy<?php endif; ?></button>
    <button type="button" class="btn-open-webhook btn-webhook-action" title="Open in new tab" aria-label="Open in new tab" data-url="<?= e($webhookUrl) ?>"><svg class="icon" aria-hidden="true"><use href="#icon-external"/></svg><?php if (!$iconOnly): ?> Open<?php endif; ?></button>
    <?php if (site_setting_bool(\App\SiteSettings::KEY_WEBHOOK_TESTING_ENABLED, true) || (isset($user) && $user && $user->isAdmin())): ?>
    <button type="button" class="btn-test-webhook btn-webhook-action" title="Test request" aria-label="Test request" data-url="<?= e($webhookUrl) ?>"><svg class="icon" aria-hidden="true"><use href="#icon-send"/></svg><?php if (!$iconOnly): ?> Test<?php endif; ?></button>
    <?php endif; ?>
</<?php echo $wrapTag; ?>>
