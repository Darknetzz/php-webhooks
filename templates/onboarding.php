<?php
$title = 'Set up PHP Webhooks';
$createError = $createError ?? null;
ob_start();
?>
<div class="onboarding">
    <h1>Welcome to PHP Webhooks</h1>
    <p class="meta" style="text-align: center; color: var(--muted); margin-bottom: 1.5rem;">Create the first account (owner) to get started.</p>
    <?php if ($createError): ?>
        <div class="error-msg"><?= e($createError) ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus placeholder="e.g. admin" value="<?= e($_POST['username'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="8" placeholder="At least 8 characters">
            <div class="hint">Minimum 8 characters.</div>
        </div>
        <button type="submit" class="btn btn-primary">Create owner account</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
