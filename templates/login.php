<?php
$title = 'Log in';
$loginError = $loginError ?? null;
$redirect = $_GET['redirect'] ?? '/';
ob_start();
?>
<div class="login-box">
    <h1>Log in</h1>
    <?php if ($loginError): ?>
        <div class="error-msg"><?= e($loginError) ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Log in</button>
    </form>
    <?php if (site_setting_bool(\App\SiteSettings::KEY_ALLOW_REGISTRATION, false)): ?>
        <p class="login-box-footer"><a href="<?= e(base_url()) ?>/register">Create an account</a></p>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
