<?php
// Test webhook request constructor modal. Include once in layout.
// Opens when .btn-test-webhook is clicked (data-url required). Sends request via fetch() and shows response.
?>
<?php $webhookTestTimeoutSeconds = isset($webhookTestTimeoutSeconds) ? (int) $webhookTestTimeoutSeconds : 30; $webhookTestTimeoutSeconds = max(5, min(300, $webhookTestTimeoutSeconds)); ?>
<div class="modal-overlay" id="test-webhook-modal" role="dialog" aria-modal="true" aria-labelledby="test-webhook-modal-title" data-timeout-seconds="<?= (int) $webhookTestTimeoutSeconds ?>">
    <div class="modal modal--wide" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2 id="test-webhook-modal-title">Test webhook</h2>
            <button type="button" class="modal-close" data-close="test-webhook-modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="test-webhook-form" class="test-webhook-form">
                <div class="form-group">
                    <label for="test-webhook-method">Method</label>
                    <select id="test-webhook-method" name="method">
                        <option value="GET">GET</option>
                        <option value="POST" selected>POST</option>
                        <option value="PUT">PUT</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                        <option value="HEAD">HEAD</option>
                        <option value="OPTIONS">OPTIONS</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="test-webhook-url">URL</label>
                    <input type="url" id="test-webhook-url" name="url" required placeholder="https://..."<?= empty($allowSpecifyTestUrl) ? ' readonly' : '' ?>>
                    <?php if (empty($allowSpecifyTestUrl)): ?>
                    <div class="hint">URL is fixed to the webhook (editing disabled by site settings).</div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="test-webhook-headers">Headers (optional)</label>
                    <textarea id="test-webhook-headers" name="headers" rows="3" placeholder="Content-Type: application/json&#10;X-Custom: value"></textarea>
                    <div class="hint">One header per line: <code>Name: value</code>. You can use {{timestamp}}, {{date_iso}}, {{uuid}} in body and headers.</div>
                </div>
                <div class="form-group">
                    <label for="test-webhook-body">Body (optional)</label>
                    <textarea id="test-webhook-body" name="body" rows="4" placeholder='{"key": "value"}'></textarea>
                </div>
                <div class="test-webhook-actions">
                    <button type="submit" class="btn btn-primary" id="test-webhook-send"><svg class="icon" aria-hidden="true"><use href="#icon-send"/></svg> Send request</button>
                    <button type="button" class="btn btn-ghost" data-close="test-webhook-modal">Cancel</button>
                </div>
            </form>
            <div id="test-webhook-response" class="test-webhook-response" hidden>
                <h3 class="form-section-title">Response</h3>
                <div class="test-webhook-response-meta">
                    <span id="test-webhook-response-status" class="test-webhook-response-status"></span>
                </div>
                <div class="form-group">
                    <label>Body</label>
                    <pre id="test-webhook-response-body" class="test-webhook-response-body codebox-code"></pre>
                </div>
                <p id="test-webhook-response-error" class="test-webhook-response-error" hidden></p>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var modal = document.getElementById('test-webhook-modal');
    var form = document.getElementById('test-webhook-form');
    var urlInput = document.getElementById('test-webhook-url');
    var methodSelect = document.getElementById('test-webhook-method');
    var headersInput = document.getElementById('test-webhook-headers');
    var bodyInput = document.getElementById('test-webhook-body');
    var sendBtn = document.getElementById('test-webhook-send');
    var responseEl = document.getElementById('test-webhook-response');
    var responseStatusEl = document.getElementById('test-webhook-response-status');
    var responseBodyEl = document.getElementById('test-webhook-response-body');
    var responseErrorEl = document.getElementById('test-webhook-response-error');

    function openTestModal() {
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        responseEl.hidden = true;
    }
    function closeTestModal() {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    document.body.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-test-webhook');
        if (btn) {
            e.preventDefault();
            var url = btn.getAttribute('data-url');
            if (url) {
                urlInput.value = url;
                openTestModal();
            }
        }
    });

    if (modal) {
        modal.querySelectorAll('[data-close="test-webhook-modal"]').forEach(function (btn) {
            btn.addEventListener('click', closeTestModal);
        });
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeTestModal();
        });
    }

    function substituteTestVariables(str) {
        if (!str || !str.replace) return str;
        var ts = Math.floor(Date.now() / 1000);
        var dateIso = new Date().toISOString();
        var uuid = (typeof crypto !== 'undefined' && crypto.randomUUID) ? crypto.randomUUID() : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0;
            var v = c === 'y' ? (r & 0x3 | 0x8) : r;
            return v.toString(16);
        });
        return str
            .replace(/\{\{timestamp\}\}/g, String(ts))
            .replace(/\{\{date_iso\}\}/g, dateIso)
            .replace(/\{\{uuid\}\}/g, uuid);
    }

    function parseHeaders(text) {
        var out = {};
        if (!text || !text.trim()) return out;
        text.split('\n').forEach(function (line) {
            var i = line.indexOf(':');
            if (i > 0) {
                var key = line.slice(0, i).trim();
                var val = line.slice(i + 1).trim();
                if (key) out[key] = substituteTestVariables(val);
            }
        });
        return out;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var url = urlInput.value.trim();
        if (!url) return;
        var method = methodSelect.value;
        var headers = parseHeaders(headersInput.value);
        var body = substituteTestVariables(bodyInput.value.trim());

        responseEl.hidden = false;
        responseErrorEl.hidden = true;
        responseStatusEl.textContent = 'Sending…';
        responseBodyEl.textContent = '';
        sendBtn.disabled = true;

        var timeoutSeconds = parseInt(modal.getAttribute('data-timeout-seconds'), 10) || 30;
        var controller = new AbortController();
        var timeoutId = setTimeout(function () { controller.abort(); }, timeoutSeconds * 1000);

        var opts = { method: method, headers: headers, signal: controller.signal };
        if (body && ['POST', 'PUT', 'PATCH'].indexOf(method) !== -1) {
            opts.body = body;
        }

        function statusClass(code) {
            if (code >= 100 && code < 200) return 'http-status--info';
            if (code >= 200 && code < 300) return 'http-status--success';
            if (code >= 300 && code < 400) return 'http-status--redirect';
            if (code >= 400 && code < 500) return 'http-status--client';
            if (code >= 500 && code < 600) return 'http-status--server';
            return 'http-status--neutral';
        }

        fetch(url, opts)
            .then(function (res) {
                responseStatusEl.textContent = res.status + ' ' + (res.statusText || '');
                responseStatusEl.className = 'test-webhook-response-status http-status ' + statusClass(res.status);
                return res.text();
            })
            .then(function (text) {
                try {
                    var parsed = JSON.parse(text);
                    responseBodyEl.textContent = JSON.stringify(parsed, null, 2);
                } catch (_) {
                    responseBodyEl.textContent = text || '(empty)';
                }
            })
            .catch(function (err) {
                responseStatusEl.textContent = 'Error';
                responseStatusEl.className = 'test-webhook-response-status http-status http-status--neutral';
                responseBodyEl.textContent = '';
                var msg = err.name === 'AbortError' ? 'Request timed out after ' + timeoutSeconds + ' seconds.' : (err.message || 'Request failed. If the URL is on another origin, the browser may block it (CORS). Try from the same origin or use curl.');
                responseErrorEl.textContent = msg;
                responseErrorEl.hidden = false;
            })
            .then(function () {
                clearTimeout(timeoutId);
                sendBtn.disabled = false;
            });
    });
})();
</script>
