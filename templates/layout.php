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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
</head>
<body>
<?php require __DIR__ . '/partials/icons.php'; ?>
    <header class="site-header">
        <div class="container">
            <a href="<?= e(base_url()) ?>/" class="logo">PHP Webhooks</a>
            <nav>
                <?php $user = auth()->user(); if ($user): ?>
                    <a href="<?= e(base_url()) ?>/" class="nav-link-with-icon"><svg class="icon" aria-hidden="true"><use href="#icon-webhook"/></svg> Webhooks</a>
                    <div class="user-dropdown">
                        <button type="button" class="user-dropdown-trigger" aria-expanded="false" aria-haspopup="true" aria-controls="user-menu" id="user-dropdown-btn">
                            <span class="user-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($user->username, 0, 1))) ?></span>
                            <span class="user-name"><?= e($user->username) ?></span>
                        </button>
                        <div class="user-dropdown-menu" id="user-menu" role="menu" aria-labelledby="user-dropdown-btn" hidden>
                            <a href="<?= e(base_url()) ?>/profile" role="menuitem"><svg class="icon" aria-hidden="true"><use href="#icon-user"/></svg> Profile</a>
                            <a href="<?= e(base_url()) ?>/settings" role="menuitem"><svg class="icon" aria-hidden="true"><use href="#icon-settings"/></svg> Settings</a>
                            <?php if ($user->isAdmin()): ?>
                                <a href="<?= e(base_url()) ?>/admin" role="menuitem"><svg class="icon" aria-hidden="true"><use href="#icon-admin"/></svg> Admin</a>
                            <?php endif; ?>
                            <a href="<?= e(base_url()) ?>/logout" role="menuitem"><svg class="icon" aria-hidden="true"><use href="#icon-logout"/></svg> Log out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= e(base_url()) ?>/" class="nav-link-with-icon"><svg class="icon" aria-hidden="true"><use href="#icon-webhook"/></svg> Webhooks</a>
                    <a href="<?= e(base_url()) ?>/login" class="nav-link-with-icon"><svg class="icon" aria-hidden="true"><use href="#icon-login"/></svg> Log in</a>
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
        if (btn) {
            var wrap = btn.closest('.webhook-url-wrap');
            var urlEl = wrap && wrap.querySelector('.webhook-url');
            if (urlEl) {
                var url = urlEl.textContent.trim();
                navigator.clipboard.writeText(url).then(function () {
                    var label = btn.textContent;
                    btn.textContent = 'Copied!';
                    btn.classList.add('copied');
                    setTimeout(function () { btn.textContent = label; btn.classList.remove('copied'); }, 1500);
                });
            }
            return;
        }
        btn = e.target.closest('.btn-open-webhook');
        if (btn && btn.dataset.url) {
            window.open(btn.dataset.url, '_blank', 'noopener,noreferrer');
        }
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>
    <script>
    (function () {
        document.querySelectorAll('code.json-beautify').forEach(function (code) {
            var raw = code.textContent.trim();
            if (raw === '') return;
            try {
                if (raw.startsWith('{') || raw.startsWith('[')) {
                    var parsed = JSON.parse(raw);
                    code.textContent = JSON.stringify(parsed, null, 2);
                    code.classList.add('language-json');
                    if (window.Prism) Prism.highlightElement(code);
                }
            } catch (_) {}
            code.parentElement.classList.add('request-body-code');
        });
    })();
    </script>
</body>
</html>
