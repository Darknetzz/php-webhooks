<?php
$title = 'Site settings';
$config = config();
$baseUrl = rtrim(base_url(), '/');
$adminActive = 'settings';
$webhookTestingEnabled = site_setting_bool(\App\SiteSettings::KEY_WEBHOOK_TESTING_ENABLED, true);
$allowSpecifyTestUrl = site_setting_bool(\App\SiteSettings::KEY_ALLOW_SPECIFY_TEST_URL, true);
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
    <form method="post" action="<?= e($baseUrl) ?>/admin/settings" class="settings-form">
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="webhook_testing_enabled" value="1" <?= $webhookTestingEnabled ? 'checked' : '' ?>>
                Enable webhook testing
            </label>
            <div class="hint">When enabled, users see a "Test" button next to webhook URLs that opens a modal to send a trial request.</div>
        </div>
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="allow_specify_test_url" value="1" <?= $allowSpecifyTestUrl ? 'checked' : '' ?> <?= !$webhookTestingEnabled ? 'disabled' : '' ?>>
                Allow specifying test URL
            </label>
            <div class="hint">When enabled, the URL in the test modal can be edited (e.g. to point to a different endpoint). When disabled, the URL is fixed to the webhook URL.</div>
        </div>
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="allow_registration" value="1" <?= $allowRegistration ? 'checked' : '' ?>>
                Allow user registration
            </label>
            <div class="hint">When enabled, anyone can create an account via the Register link on the login page. New users get the "user" role.</div>
        </div>
        <button type="submit" class="btn btn-primary">Save settings</button>
    </form>
</section>

<p style="margin-top: 1.5rem;"><a href="<?= e($baseUrl) ?>/admin" class="btn btn-ghost">Back to Admin</a></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
