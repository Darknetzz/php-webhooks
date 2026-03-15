<?php
$title = 'Register';
$registerError = $registerError ?? null;
$registerUsername = $registerUsername ?? '';
ob_start();
?>
<div class="login-box">
    <h1>Register</h1>
    <?php if ($registerError): ?>
        <div class="error-msg"><?= e($registerError) ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= e($registerUsername) ?>" required autofocus autocomplete="username">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirm password</label>
            <input type="password" id="password_confirm" name="password_confirm" required minlength="8" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    <p class="login-box-footer"><a href="<?= e(base_url()) ?>/login">Already have an account? Log in</a></p>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
