<?php
$title = $title ?? 'PHP Webhooks';
$config = config();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="<?= e(base_url()) ?>/assets/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="<?= e(base_url()) ?>/" class="logo">PHP Webhooks</a>
            <nav>
                <?php $user = auth()->user(); if ($user): ?>
                    <a href="<?= e(base_url()) ?>/">Dashboard</a>
                    <a href="<?= e(base_url()) ?>/admin/webhooks">Create Webhook</a>
                    <span class="user"><?= e($user->username) ?></span>
                    <a href="<?= e(base_url()) ?>/logout">Log out</a>
                <?php else: ?>
                    <a href="<?= e(base_url()) ?>/">Webhooks</a>
                    <a href="<?= e(base_url()) ?>/login">Log in</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="container">
        <?php if (!empty($flash)): ?>
            <div class="flash"><?= e($flash) ?></div>
        <?php endif; ?>
        <?= $content ?? '' ?>
    </main>
    <footer class="site-footer">
        <div class="container">Self-hosted webhook receiver</div>
    </footer>
</body>
</html>
