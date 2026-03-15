<?php
// Reusable code block with copy button. Expects $code (raw string to copy/display), optional $language = 'bash'|'text'.
// data-copy = exact raw text for Copy. For bash, highlighting is applied client-side to avoid server output stripping.
$language = $language ?? 'text';
?>
<div class="codebox">
    <div class="codebox-toolbar">
        <button type="button" class="btn-copy-codebox btn btn-ghost" title="Copy"><svg class="icon" aria-hidden="true"><use href="#icon-copy"/></svg> Copy</button>
    </div>
    <pre class="codebox-pre"><code class="codebox-code codebox-<?= e($language) ?>" data-copy="<?= e($code) ?>"<?= ($language === 'bash') ? ' data-highlight="curl"' : '' ?>><?= e($code) ?></code></pre>
</div>
