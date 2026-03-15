<?php
$title = $title ?? 'PHP Webhooks';
$config = config();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(trim(site_setting(\App\SiteSettings::KEY_SITE_NAME, '')) !== '' ? $title . ' — ' . trim(site_setting(\App\SiteSettings::KEY_SITE_NAME, '')) : $title) ?></title>
    <script>
    (function(){
        var t=localStorage.getItem('webhooks_theme')||'dark';
        var a=localStorage.getItem('webhooks_accent')||'#22d3ee';
        var h=localStorage.getItem('webhooks_accent_hover')||'#06b6d4';
        document.documentElement.dataset.theme=t;
        document.documentElement.style.setProperty('--accent',a);
        document.documentElement.style.setProperty('--accent-hover',h);
    })();
    </script>
    <link rel="icon" type="image/svg+xml" href="<?= e(base_url()) ?>/assets/favicon.svg">
    <link rel="stylesheet" href="<?= e(base_url()) ?>/assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
</head>
<body>
<?php require __DIR__ . '/partials/icons.php'; ?>
<?php
$user = auth()->user();
$showWebhookTesting = site_setting_bool(\App\SiteSettings::KEY_WEBHOOK_TESTING_ENABLED, true) || ($user && $user->isAdmin());
if ($showWebhookTesting):
    $allowSpecifyTestUrl = site_setting_bool(\App\SiteSettings::KEY_ALLOW_SPECIFY_TEST_URL, true);
    $webhookTestTimeoutSeconds = (int) (site_setting(\App\SiteSettings::KEY_WEBHOOK_TEST_TIMEOUT_SECONDS, '30') ?: '30');
    $webhookTestTimeoutSeconds = max(5, min(300, $webhookTestTimeoutSeconds));
?>
<?php require __DIR__ . '/partials/webhook_test_modal.php'; ?>
<?php endif; ?>
    <header class="site-header">
        <div class="container">
            <?php $siteName = trim(site_setting(\App\SiteSettings::KEY_SITE_NAME, '')); $logoLabel = $siteName !== '' ? $siteName : 'PHP Webhooks'; ?>
            <a href="<?= e(base_url()) ?>/" class="logo"><img src="<?= e(base_url()) ?>/assets/favicon.svg" alt="" class="logo-favicon" width="24" height="24"> <?= e($logoLabel) ?></a>
            <nav>
                <?php $user = auth()->user(); if ($user): ?>
                    <a href="<?= e(base_url()) ?>/" class="nav-link-with-icon"><svg class="icon" aria-hidden="true"><use href="#icon-webhook"/></svg> Webhooks</a>
                    <div class="user-dropdown">
                        <button type="button" class="user-dropdown-trigger" aria-expanded="false" aria-haspopup="true" aria-controls="user-menu" id="user-dropdown-btn">
                            <span class="user-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($user->username, 0, 1))) ?></span>
                            <span class="user-name"><?= e($user->username) ?></span>
                        </button>
                        <div class="user-dropdown-menu" id="user-menu" role="menu" aria-labelledby="user-dropdown-btn" hidden>
                            <div class="user-dropdown-menu-role" aria-hidden="true">
                                <?php $role = $user->role; require __DIR__ . '/partials/role_display.php'; ?>
                            </div>
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
                    <?php if (site_setting_bool(\App\SiteSettings::KEY_ALLOW_REGISTRATION, false)): ?>
                        <a href="<?= e(base_url()) ?>/register" class="nav-link-with-icon"><svg class="icon" aria-hidden="true"><use href="#icon-user"/></svg> Register</a>
                    <?php endif; ?>
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
            <?php $version = git_version(); $repoUrl = git_repo_url(); $repoName = git_repo_name(); if ($version !== null || $repoName !== null): ?>
                · <?php if ($repoUrl !== null): ?><a href="<?= e($repoUrl) ?>" target="_blank" rel="noopener noreferrer" class="footer-repo-link"><?php endif; ?><svg class="icon icon-github" aria-hidden="true"><use href="#icon-github"/></svg> <?= $repoName !== null ? e($repoName) : 'Repository' ?><?php if ($repoUrl !== null): ?></a><?php endif; ?>
                <?php if ($version !== null): ?> <?php if ($repoUrl !== null): ?><a href="<?= e($repoUrl) ?>/commit/<?= e($version['commit']) ?>" target="_blank" rel="noopener noreferrer"><?php endif; ?><?= e($version['tag'] ?? $version['commit']) ?><?= $version['tag'] !== null ? ' <code>' . e($version['commit']) . '</code>' : '' ?><?php if ($repoUrl !== null): ?></a><?php endif; ?><?php endif; ?>
            <?php endif; ?>
        </div>
    </footer>
    <script>
    document.body.addEventListener('change', function (e) {
        var toggle = e.target.closest('.specify-allowed-methods-toggle');
        if (toggle) {
            var section = toggle.closest('.allowed-methods-toggle-section');
            var inner = section && section.querySelector('.allowed-methods-inner');
            if (inner) {
                inner.style.display = toggle.checked ? 'block' : 'none';
                inner.querySelectorAll('input[name="allowed_methods[]"]').forEach(function (cb) {
                    cb.disabled = !toggle.checked;
                });
            }
            return;
        }
    });
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
        btn = e.target.closest('.btn-copy-codebox');
        if (btn) {
            var codebox = btn.closest('.codebox');
            var codeEl = codebox && codebox.querySelector('.codebox-code');
            if (codeEl) {
                var text = (codeEl.getAttribute('data-copy') || codeEl.textContent || '').trim();
                navigator.clipboard.writeText(text).then(function () {
                    var label = btn.innerHTML;
                    btn.innerHTML = '<svg class="icon" aria-hidden="true"><use href="#icon-copy"/></svg> Copied!';
                    btn.classList.add('copied');
                    setTimeout(function () { btn.innerHTML = label; btn.classList.remove('copied'); }, 1500);
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
        function escapeHtml(s) {
            return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }
        document.querySelectorAll('.codebox-code[data-highlight="curl"]').forEach(function (el) {
            var text = el.textContent || '';
            var s = escapeHtml(text)
                .replace(/^(curl)\b/, '<span class="sh-cmd">$1</span>')
                .replace(/\b(-X\s+[A-Z]+)\b/g, '<span class="sh-opt">$1</span>')
                .replace(/\b(-H)\b/g, '<span class="sh-opt">$1</span>')
                .replace(/\b(-d)\b/g, '<span class="sh-opt">$1</span>')
                .replace(/&quot;(.*?)&quot;/, '<span class="sh-str">&quot;$1&quot;</span>')
                .replace(/&#39;(.*?)&#39;/g, '<span class="sh-str">&#39;$1&#39;</span>');
            el.innerHTML = s;
        });
    })();

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
