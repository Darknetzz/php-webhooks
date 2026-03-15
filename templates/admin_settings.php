<?php
$title = 'Site settings';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$adminActive = 'settings';

// General
$siteName = site_setting(\App\SiteSettings::KEY_SITE_NAME, '');

// Webhooks
$webhookTestingEnabled = site_setting_bool(\App\SiteSettings::KEY_WEBHOOK_TESTING_ENABLED, true);
$allowSpecifyTestUrl = site_setting_bool(\App\SiteSettings::KEY_ALLOW_SPECIFY_TEST_URL, true);
$maxWebhooksPerUser = (int) (site_setting(\App\SiteSettings::KEY_MAX_WEBHOOKS_PER_USER, '0') ?: '0');
$webhookTestTimeoutSeconds = (int) (site_setting(\App\SiteSettings::KEY_WEBHOOK_TEST_TIMEOUT_SECONDS, '30') ?: '30');
$webhookTestTimeoutSeconds = max(5, min(300, $webhookTestTimeoutSeconds));

// Access & security
$allowRegistration = site_setting_bool(\App\SiteSettings::KEY_ALLOW_REGISTRATION, false);

$settingsSaved = $settingsSaved ?? false;
ob_start();
?>
<h1>Site settings</h1>
<?php require __DIR__ . '/partials/admin_nav_pills.php'; ?>

<?php if ($settingsSaved): ?>
    <div class="flash" style="margin-bottom: 1rem;">Settings saved.</div>
<?php endif; ?>

<section class="settings-section card">
    <h2 class="settings-section-title">General</h2>
    <form method="post" action="<?= e($baseUrl) ?>/admin/settings" class="settings-form" id="settings-form-general">
        <input type="hidden" name="settings_section" value="general">
        <div class="form-group">
            <label for="site_name">Site name</label>
            <input type="text" id="site_name" name="site_name" value="<?= e($siteName) ?>" placeholder="PHP Webhooks" maxlength="100">
            <div class="hint">Shown in the header and browser title. Leave empty for the default “PHP Webhooks”.</div>
        </div>
        <button type="submit" class="btn btn-primary">Save General</button>
    </form>
</section>

<section class="settings-section card">
    <h2 class="settings-section-title">Webhooks</h2>
    <form method="post" action="<?= e($baseUrl) ?>/admin/settings" class="settings-form" id="settings-form-webhooks">
        <input type="hidden" name="settings_section" value="webhooks">
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="webhook_testing_enabled" value="1" <?= $webhookTestingEnabled ? 'checked' : '' ?>>
                Enable webhook testing
            </label>
            <div class="hint">When enabled, users see a “Test” button next to webhook URLs that opens a modal to send a trial request.</div>
        </div>
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="allow_specify_test_url" value="1" <?= $allowSpecifyTestUrl ? 'checked' : '' ?> <?= !$webhookTestingEnabled ? 'disabled' : '' ?>>
                Allow specifying test URL
            </label>
            <div class="hint">When enabled, the URL in the test modal can be edited (e.g. to point to a different endpoint). When disabled, the URL is fixed to the webhook URL.</div>
        </div>
        <div class="form-group">
            <label for="webhook_test_timeout_seconds">Test request timeout (seconds)</label>
            <input type="number" id="webhook_test_timeout_seconds" name="webhook_test_timeout_seconds" value="<?= (int) $webhookTestTimeoutSeconds ?>" min="5" max="300" step="1">
            <div class="hint">How long to wait for a response when using the Test button (5–300 seconds).</div>
        </div>
        <div class="form-group">
            <label for="max_webhooks_per_user">Max webhooks per user</label>
            <input type="number" id="max_webhooks_per_user" name="max_webhooks_per_user" value="<?= $maxWebhooksPerUser ?: '' ?>" min="0" step="1" placeholder="0">
            <div class="hint">Maximum webhooks each user can create. Use 0 for unlimited.</div>
        </div>
        <button type="submit" class="btn btn-primary">Save Webhooks</button>
    </form>
</section>

<section class="settings-section card">
    <h2 class="settings-section-title">Access &amp; security</h2>
    <form method="post" action="<?= e($baseUrl) ?>/admin/settings" class="settings-form" id="settings-form-access">
        <input type="hidden" name="settings_section" value="access">
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="allow_registration" value="1" <?= $allowRegistration ? 'checked' : '' ?>>
                Allow user registration
            </label>
            <div class="hint">When enabled, anyone can create an account via the Register link on the login page. New users get the “user” role.</div>
        </div>
        <button type="submit" class="btn btn-primary">Save Access &amp; security</button>
    </form>
</section>

<p style="margin-top: 1.5rem;"><a href="<?= e($baseUrl) ?>/admin" class="btn btn-ghost">Back to Admin</a></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
