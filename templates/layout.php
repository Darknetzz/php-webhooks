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
                    <div class="user-dropdown">
                        <button type="button" class="user-dropdown-trigger" aria-expanded="false" aria-haspopup="true" aria-controls="user-menu" id="user-dropdown-btn">
                            <span class="user-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($user->username, 0, 1))) ?></span>
                            <span class="user-name"><?= e($user->username) ?></span>
                        </button>
                        <div class="user-dropdown-menu" id="user-menu" role="menu" aria-labelledby="user-dropdown-btn" hidden>
                            <a href="<?= e(base_url()) ?>/profile" role="menuitem">Profile</a>
                            <a href="<?= e(base_url()) ?>/settings" role="menuitem">Settings</a>
                            <a href="<?= e(base_url()) ?>/logout" role="menuitem">Log out</a>
                        </div>
                    </div>
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
        <div class="container">
            Self-hosted webhook receiver
            <?php $version = git_version(); $repoUrl = git_repo_url(); if ($version !== null): ?>
                · Version <?php if ($repoUrl !== null): ?><a href="<?= e($repoUrl) ?>/commit/<?= e($version['commit']) ?>" target="_blank" rel="noopener noreferrer"><?php endif; ?><?= e($version['tag'] ?? $version['commit']) ?><?= $version['tag'] !== null ? ' <code>' . e($version['commit']) . '</code>' : '' ?><?php if ($repoUrl !== null): ?></a><?php endif; ?>
            <?php endif; ?>
        </div>
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

    (function () {
        var trigger = document.getElementById('user-dropdown-btn');
        var menu = document.getElementById('user-menu');
        if (!trigger || !menu) return;
        function open() {
            menu.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
        }
        function close() {
            menu.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
        }
        function toggle() {
            if (menu.hidden) open(); else close();
        }
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            toggle();
        });
        document.addEventListener('click', function () {
            if (!menu.hidden) close();
        });
        menu.addEventListener('click', function (e) {
            if (e.target.tagName === 'A') close();
        });
    })();
    </script>
</body>
</html>
