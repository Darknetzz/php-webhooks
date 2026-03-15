<?php
// Reusable code block with copy button. Expects $code (raw string to copy/display), optional $language = 'bash'|'text'.
// Display is escaped only (no HTML) so the copyable command is never mangled; copy uses data-copy for exact text.
$language = $language ?? 'text';
?>
<div class="codebox">
    <div class="codebox-toolbar">
        <button type="button" class="btn-copy-codebox btn btn-ghost" title="Copy"><svg class="icon" aria-hidden="true"><use href="#icon-copy"/></svg> Copy</button>
    </div>
    <pre class="codebox-pre"><code class="codebox-code codebox-<?= e($language) ?>" data-copy="<?= e($code) ?>"><?= e($code) ?></code></pre>
</div>
