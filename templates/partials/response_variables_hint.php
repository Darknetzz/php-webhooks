<?php
// Hint for webhook response body/headers: variables are supported. Include in edit/create webhook forms.
// Renders a "Variables" toggle that expands to show placeholders in a highlighted list.
?>
<details class="variables-collapse" style="margin-bottom: 0.75rem;">
    <summary class="variables-toggle">Variables</summary>
    <div class="variables-panel">
        <p class="variables-intro">Use these placeholders in response headers and body. They are replaced with the incoming request data when the webhook is called.</p>
        <dl class="variables-list">
            <dt><code class="variable-placeholder">{{request.method}}</code></dt>
            <dd>HTTP method (GET, POST, etc.)</dd>
            <dt><code class="variable-placeholder">{{request.ip}}</code></dt>
            <dd>Client IP address</dd>
            <dt><code class="variable-placeholder">{{request.body}}</code></dt>
            <dd>Raw request body string</dd>
            <dt><code class="variable-placeholder">{{request.body.key}}</code></dt>
            <dd>JSON body field (use dot path, e.g. <code class="variable-placeholder">{{request.body.data.id}}</code>)</dd>
            <dt><code class="variable-placeholder">{{request.headers.X-Name}}</code></dt>
            <dd>Request header (replace <code>X-Name</code> with header name)</dd>
            <dt><code class="variable-placeholder">{{request.query.param}}</code></dt>
            <dd>Query string parameter</dd>
        </dl>
    </div>
</details>
