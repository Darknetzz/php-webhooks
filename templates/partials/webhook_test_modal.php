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
                <div class="form-group test-webhook-headers-group">
                    <label>Headers (optional)</label>
                    <div class="test-webhook-mode" role="tablist" aria-label="Headers input mode">
                        <button type="button" class="test-webhook-mode-btn is-active" data-mode="preset" aria-selected="true">Preset</button>
                        <button type="button" class="test-webhook-mode-btn" data-mode="manual" aria-selected="false">Manual</button>
                    </div>
                    <div id="test-webhook-headers-preset" class="test-webhook-preset-block">
                        <div class="table-wrap">
                            <table class="test-webhook-kv-table" id="test-webhook-headers-table">
                                <thead>
                                    <tr><th>Header</th><th>Value</th><th class="table-cell-actions"></th></tr>
                                </thead>
                                <tbody id="test-webhook-headers-tbody"></tbody>
                            </table>
                        </div>
                        <button type="button" class="test-webhook-add-row btn btn-ghost btn-sm" id="test-webhook-add-header" aria-label="Add header">+ Add header</button>
                    </div>
                    <div id="test-webhook-headers-manual" class="test-webhook-manual-block" hidden>
                        <textarea id="test-webhook-headers" name="headers" rows="3" placeholder="Content-Type: application/json&#10;X-Custom: value"></textarea>
                    </div>
                    <div class="hint">You can use {{timestamp}}, {{date_iso}}, {{uuid}} in body and headers.</div>
                </div>
                <div class="form-group test-webhook-body-group">
                    <label>Body (optional)</label>
                    <div class="test-webhook-mode" role="tablist" aria-label="Body input mode">
                        <button type="button" class="test-webhook-mode-btn is-active" data-mode="kv" aria-selected="true">Key/Value</button>
                        <button type="button" class="test-webhook-mode-btn" data-mode="manual" aria-selected="false">Manual</button>
                    </div>
                    <div id="test-webhook-body-kv" class="test-webhook-preset-block">
                        <div class="table-wrap">
                            <table class="test-webhook-kv-table" id="test-webhook-body-table">
                                <thead>
                                    <tr><th>Key</th><th>Value</th><th class="table-cell-actions"></th></tr>
                                </thead>
                                <tbody id="test-webhook-body-tbody"></tbody>
                            </table>
                        </div>
                        <button type="button" class="test-webhook-add-row btn btn-ghost btn-sm" id="test-webhook-add-body-row" aria-label="Add row">+ Add row</button>
                    </div>
                    <div id="test-webhook-body-manual" class="test-webhook-manual-block" hidden>
                        <textarea id="test-webhook-body" name="body" rows="4" placeholder='{"key": "value"}'></textarea>
                    </div>
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

    var PRESET_HEADERS = [
        { name: 'Content-Type', placeholder: 'application/json' },
        { name: 'Authorization', placeholder: 'Bearer token' },
        { name: 'Accept', placeholder: 'application/json' },
        { name: 'X-Request-ID', placeholder: '{{uuid}}' },
        { name: 'X-Correlation-ID', placeholder: '{{uuid}}' },
        { name: 'User-Agent', placeholder: '' },
        { name: '__custom__', placeholder: '', label: 'Custom' }
    ];

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

    function getHeaderNameFromRow(row) {
        var sel = row.querySelector('.test-webhook-header-select');
        var val = sel ? sel.value : '';
        if (val === '__custom__') {
            var customInput = row.querySelector('.test-webhook-header-custom-name');
            return customInput ? customInput.value.trim() : '';
        }
        return val || '';
    }

    function buildHeadersFromPreset() {
        var out = {};
        var tbody = document.getElementById('test-webhook-headers-tbody');
        if (!tbody) return out;
        [].slice.call(tbody.querySelectorAll('tr')).forEach(function (row) {
            var name = getHeaderNameFromRow(row);
            var valInput = row.querySelector('.test-webhook-header-value');
            var val = valInput ? valInput.value.trim() : '';
            if (name && val) out[name] = substituteTestVariables(val);
        });
        return out;
    }

    function buildBodyFromKV() {
        var obj = {};
        var tbody = document.getElementById('test-webhook-body-tbody');
        if (!tbody) return '';
        [].slice.call(tbody.querySelectorAll('tr')).forEach(function (row) {
            var keyInput = row.querySelector('.test-webhook-body-key');
            var valInput = row.querySelector('.test-webhook-body-value');
            var key = keyInput ? keyInput.value.trim() : '';
            var val = valInput ? valInput.value : '';
            if (key) obj[key] = val;
        });
        if (Object.keys(obj).length === 0) return '';
        return JSON.stringify(obj);
    }

    function addHeaderRow(valueName, valuePlaceholder) {
        valueName = valueName || 'Content-Type';
        valuePlaceholder = valuePlaceholder != null ? valuePlaceholder : 'application/json';
        var tbody = document.getElementById('test-webhook-headers-tbody');
        var tr = document.createElement('tr');
        var optHtml = PRESET_HEADERS.map(function (h) {
            var label = h.label || h.name;
            return '<option value="' + (h.name === '__custom__' ? '__custom__' : h.name) + '">' + label + '</option>';
        }).join('');
        tr.innerHTML =
            '<td><select class="test-webhook-header-select">' + optHtml + '</select>' +
            '<input type="text" class="test-webhook-header-custom-name" placeholder="Header name" style="display:none; margin-top: 4px;" /></td>' +
            '<td><input type="text" class="test-webhook-header-value" placeholder="' + (valuePlaceholder || '') + '" /></td>' +
            '<td class="table-cell-actions"><button type="button" class="btn btn-ghost btn-sm test-webhook-remove-row" aria-label="Remove">×</button></td>';
        var sel = tr.querySelector('.test-webhook-header-select');
        var customInput = tr.querySelector('.test-webhook-header-custom-name');
        sel.value = valueName === '__custom__' ? '__custom__' : valueName;
        if (valueName === '__custom__') customInput.style.display = 'block';
        sel.addEventListener('change', function () {
            customInput.style.display = sel.value === '__custom__' ? 'block' : 'none';
        });
        var ph = PRESET_HEADERS.find(function (h) { return h.name === (valueName === '__custom__' ? '__custom__' : valueName); });
        if (ph && ph.placeholder) tr.querySelector('.test-webhook-header-value').placeholder = ph.placeholder;
        tr.querySelector('.test-webhook-remove-row').addEventListener('click', function () { tr.remove(); });
        tbody.appendChild(tr);
    }

    function addBodyRow() {
        var tbody = document.getElementById('test-webhook-body-tbody');
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td><input type="text" class="test-webhook-body-key" placeholder="Key" /></td>' +
            '<td><input type="text" class="test-webhook-body-value" placeholder="Value" /></td>' +
            '<td class="table-cell-actions"><button type="button" class="btn btn-ghost btn-sm test-webhook-remove-row" aria-label="Remove">×</button></td>';
        tr.querySelector('.test-webhook-remove-row').addEventListener('click', function () { tr.remove(); });
        tbody.appendChild(tr);
    }

    document.getElementById('test-webhook-add-header').addEventListener('click', function () { addHeaderRow(); });
    document.getElementById('test-webhook-add-body-row').addEventListener('click', function () { addBodyRow(); });

    addHeaderRow('Content-Type', 'application/json');

    (function () {
        var r = document.createElement('tr');
        r.innerHTML =
            '<td><input type="text" class="test-webhook-body-key" placeholder="Key" /></td>' +
            '<td><input type="text" class="test-webhook-body-value" placeholder="Value" /></td>' +
            '<td class="table-cell-actions"><button type="button" class="btn btn-ghost btn-sm test-webhook-remove-row" aria-label="Remove">×</button></td>';
        r.querySelector('.test-webhook-remove-row').addEventListener('click', function () { r.remove(); });
        document.getElementById('test-webhook-body-tbody').appendChild(r);
    })();

    function setupModeSwitch(containerSelector, presetBlockId, manualBlockId, presetValue, manualValue) {
        var container = document.querySelector(containerSelector);
        if (!container) return;
        var presetBlock = document.getElementById(presetBlockId);
        var manualBlock = document.getElementById(manualBlockId);
        container.querySelectorAll('.test-webhook-mode-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var mode = btn.getAttribute('data-mode');
                container.querySelectorAll('.test-webhook-mode-btn').forEach(function (b) {
                    b.classList.toggle('is-active', b === btn);
                    b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
                });
                if (mode === presetValue) {
                    presetBlock.hidden = false;
                    manualBlock.hidden = true;
                } else {
                    presetBlock.hidden = true;
                    manualBlock.hidden = false;
                }
            });
        });
    }
    setupModeSwitch('.test-webhook-headers-group', 'test-webhook-headers-preset', 'test-webhook-headers-manual', 'preset', 'manual');
    setupModeSwitch('.test-webhook-body-group', 'test-webhook-body-kv', 'test-webhook-body-manual', 'kv', 'manual');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var url = urlInput.value.trim();
        if (!url) return;
        var method = methodSelect.value;
        var headersMode = form.querySelector('.test-webhook-headers-group .test-webhook-mode-btn.is-active');
        var bodyMode = form.querySelector('.test-webhook-body-group .test-webhook-mode-btn.is-active');
        var headers = (headersMode && headersMode.getAttribute('data-mode') === 'preset')
            ? buildHeadersFromPreset()
            : parseHeaders(headersInput.value);
        var body = (bodyMode && bodyMode.getAttribute('data-mode') === 'kv')
            ? buildBodyFromKV()
            : bodyInput.value.trim();
        if (body) body = substituteTestVariables(body);
        if (body && ['POST', 'PUT', 'PATCH'].indexOf(method) !== -1 && !headers['Content-Type']) {
            headers['Content-Type'] = 'application/json';
        }

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
