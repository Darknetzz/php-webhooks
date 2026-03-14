<?php
$title = 'Settings';
$config = config();
ob_start();
?>
<h1>Settings</h1>
<div class="card">
    <p style="margin: 0; color: var(--muted);">Application and account settings can be configured here.</p>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
