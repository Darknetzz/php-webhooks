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
    <link rel="stylesheet" href="<?= e($config['url']) ?>/assets/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="<?= e($config['url']) ?>/" class="logo">PHP Webhooks</a>
            <nav>
                <?php $user = auth()->user(); if ($user): ?>
                    <a href="<?= e($config['url']) ?>/">Dashboard</a>
                    <a href="<?= e($config['url']) ?>/admin/webhooks">My Webhooks</a>
                    <span class="user"><?= e($user->username) ?></span>
                    <a href="<?= e($config['url']) ?>/logout">Log out</a>
                <?php else: ?>
                    <a href="<?= e($config['url']) ?>/">Webhooks</a>
                    <a href="<?= e($config['url']) ?>/login">Log in</a>
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
