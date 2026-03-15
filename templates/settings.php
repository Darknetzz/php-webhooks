<?php
$title = 'Settings';
$config = config();
$passwordError = $passwordError ?? null;
$passwordSuccess = $passwordSuccess ?? false;
ob_start();
?>
<h1>Settings</h1>

<section class="settings-section card">
    <h2 class="settings-section-title">Appearance</h2>
    <div class="settings-row">
        <label class="settings-label">Theme</label>
        <div class="theme-toggle" role="group" aria-label="Theme">
            <button type="button" class="theme-option active" data-theme="dark" aria-pressed="true">Dark</button>
            <button type="button" class="theme-option" data-theme="light" aria-pressed="false">Light</button>
        </div>
    </div>
    <div class="settings-row">
        <label class="settings-label">Primary color</label>
        <div class="primary-color-swatches" role="group" aria-label="Primary color">
            <?php foreach (primary_color_presets() as $key => $colors): list($main, $hover) = $colors; ?>
            <button type="button" class="color-swatch" data-color="<?= e($key) ?>" data-accent="<?= e($main) ?>" data-accent-hover="<?= e($hover) ?>" style="--swatch: <?= e($main) ?>" title="<?= e(ucfirst($key)) ?>" aria-pressed="false"></button>
            <?php endforeach; ?>
        </div>
        <input type="color" id="primary-color-picker" class="color-picker" value="<?= e(site_primary_color()[0]) ?>" aria-label="Custom primary color" title="Custom color">
        <button type="button" class="btn btn-ghost btn-use-site-default" id="btn-use-site-default" style="margin-left: 0.5rem;">Use site default</button>
        <div class="hint" style="margin-top: 0.35rem;">Overrides the default set by the site admin. Clear with “Use site default” to follow the site theme.</div>
    </div>
</section>

<section class="settings-section card">
    <h2 class="settings-section-title">Account</h2>
    <h3 class="settings-subtitle">Change password</h3>
    <?php if ($passwordSuccess): ?>
        <div class="flash settings-flash">Password updated successfully.</div>
    <?php endif; ?>
    <?php if ($passwordError): ?>
        <div class="error-msg settings-flash"><?= e($passwordError) ?></div>
    <?php endif; ?>
    <form method="post" action="" class="settings-form">
        <input type="hidden" name="change_password" value="1">
        <div class="form-group">
            <label for="current_password">Current password</label>
            <input type="password" id="current_password" name="current_password" required autocomplete="current-password" placeholder="Current password">
        </div>
        <div class="form-group">
            <label for="new_password">New password</label>
            <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password" placeholder="New password (at least 8 characters)">
        </div>
        <div class="form-group">
            <label for="new_password_confirm">Confirm new password</label>
            <input type="password" id="new_password_confirm" name="new_password_confirm" required minlength="8" autocomplete="new-password" placeholder="Repeat new password">
        </div>
        <button type="submit" class="btn btn-primary">Update password</button>
    </form>
</section>

<script>
(function () {
    var STORAGE_THEME = 'webhooks_theme';
    var STORAGE_ACCENT = 'webhooks_accent';
    var STORAGE_ACCENT_HOVER = 'webhooks_accent_hover';
    var siteDefault = <?= json_encode(site_primary_color()) ?>;

    function applyTheme(theme) {
        document.documentElement.dataset.theme = theme || 'dark';
    }

    function applyAccent(accent, accentHover) {
        var root = document.documentElement.style;
        root.setProperty('--accent', accent || siteDefault[0] || '#22d3ee');
        root.setProperty('--accent-hover', accentHover || siteDefault[1] || '#06b6d4');
    }

    function loadSaved() {
        var theme = localStorage.getItem(STORAGE_THEME) || 'dark';
        var accent = localStorage.getItem(STORAGE_ACCENT) || siteDefault[0] || '#22d3ee';
        var accentHover = localStorage.getItem(STORAGE_ACCENT_HOVER) || siteDefault[1] || '#06b6d4';
        applyTheme(theme);
        applyAccent(accent, accentHover);
        document.querySelectorAll('.theme-option').forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.theme === theme);
            btn.setAttribute('aria-pressed', btn.dataset.theme === theme ? 'true' : 'false');
        });
        document.querySelectorAll('.color-swatch').forEach(function (btn) {
            var isActive = btn.dataset.accent === accent;
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
        var picker = document.getElementById('primary-color-picker');
        if (picker) picker.value = accent;
    }

    loadSaved();

    document.querySelectorAll('.theme-option').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var theme = this.dataset.theme;
            localStorage.setItem(STORAGE_THEME, theme);
            applyTheme(theme);
            document.querySelectorAll('.theme-option').forEach(function (b) {
                b.classList.toggle('active', b === btn);
                b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
            });
        });
    });

    document.querySelectorAll('.color-swatch').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var accent = this.dataset.accent;
            var accentHover = this.dataset.accentHover;
            localStorage.setItem(STORAGE_ACCENT, accent);
            localStorage.setItem(STORAGE_ACCENT_HOVER, accentHover);
            applyAccent(accent, accentHover);
            document.querySelectorAll('.color-swatch').forEach(function (b) {
                b.classList.toggle('active', b === btn);
                b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
            });
            var picker = document.getElementById('primary-color-picker');
            if (picker) picker.value = accent;
        });
    });

    var picker = document.getElementById('primary-color-picker');
    if (picker) {
        picker.addEventListener('input', function () {
            var hex = this.value;
            var hover = shadeHex(hex, -12);
            localStorage.setItem(STORAGE_ACCENT, hex);
            localStorage.setItem(STORAGE_ACCENT_HOVER, hover);
            applyAccent(hex, hover);
            document.querySelectorAll('.color-swatch').forEach(function (b) {
                b.classList.remove('active');
                b.setAttribute('aria-pressed', 'false');
            });
        });
    }

    var btnUseSiteDefault = document.getElementById('btn-use-site-default');
    if (btnUseSiteDefault) {
        btnUseSiteDefault.addEventListener('click', function () {
            localStorage.removeItem(STORAGE_ACCENT);
            localStorage.removeItem(STORAGE_ACCENT_HOVER);
            var accent = siteDefault[0] || '#22d3ee';
            var accentHover = siteDefault[1] || '#06b6d4';
            applyAccent(accent, accentHover);
            document.querySelectorAll('.color-swatch').forEach(function (b) {
                var isActive = b.dataset.accent === accent;
                b.classList.toggle('active', isActive);
                b.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
            if (picker) picker.value = accent;
        });
    }

    function shadeHex(hex, pct) {
        var num = parseInt(hex.slice(1), 16);
        var r = (num >> 16) & 0xff;
        var g = (num >> 8) & 0xff;
        var b = num & 0xff;
        var f = 1 - Math.abs(pct) / 100;
        if (pct < 0) {
            r = Math.round(r * f);
            g = Math.round(g * f);
            b = Math.round(b * f);
        } else {
            r = Math.round(r + (255 - r) * (1 - f));
            g = Math.round(g + (255 - g) * (1 - f));
            b = Math.round(b + (255 - b) * (1 - f));
        }
        return '#' + (0x1000000 + (r << 16) + (g << 8) + b).toString(16).slice(1);
    }
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
