<?php
// Reusable code block with copy button. Expects $code (raw string to copy/display), optional $language = 'bash'|'text'.
// data-copy holds the exact raw text so Copy always pastes correctly; display may use syntax-highlight HTML.
$language = $language ?? 'text';
$display = ($language === 'bash') ? highlight_curl_for_display($code) : e($code);
?>
<div class="codebox">
    <div class="codebox-toolbar">
        <button type="button" class="btn-copy-codebox btn btn-ghost" title="Copy"><svg class="icon" aria-hidden="true"><use href="#icon-copy"/></svg> Copy</button>
    </div>
    <pre class="codebox-pre"><code class="codebox-code codebox-<?= e($language) ?>" data-copy="<?= e($code) ?>"><?= $display ?></code></pre>
</div>
