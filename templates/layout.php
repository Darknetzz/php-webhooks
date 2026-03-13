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
    <script>
    document.body.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-copy-webhook');
        if (!btn) return;
        var wrap = btn.closest('.webhook-url-wrap');
        var urlEl = wrap && wrap.querySelector('.webhook-url');
        if (!urlEl) return;
        var url = urlEl.textContent.trim();
        navigator.clipboard.writeText(url).then(function () {
            var label = btn.textContent;
            btn.textContent = 'Copied!';
            btn.classList.add('copied');
            setTimeout(function () {
                btn.textContent = label;
                btn.classList.remove('copied');
            }, 1500);
        });
    });
    </script>
</body>
</html>
