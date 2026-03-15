<?php
// Shared script for response headers/body Preset and Key-Value + Manual modes in create/edit webhook forms.
// Included inside a <script> block by layout.php — do not output <script> tags here.
?>
(function () {
    var PRESET_HEADERS = [
        { name: 'Content-Type', placeholder: 'application/json' },
        { name: 'Authorization', placeholder: 'Bearer token' },
        { name: 'Accept', placeholder: 'application/json' },
        { name: 'X-Request-ID', placeholder: '{{request.headers.X-Request-ID}}' },
        { name: 'X-Correlation-ID', placeholder: '{{uuid}}' },
        { name: 'User-Agent', placeholder: '' },
        { name: '__custom__', placeholder: '', label: 'Custom' }
    ];

    function getHeaderNameFromRow(row) {
        var sel = row.querySelector('.webhook-form-header-select');
        var val = sel ? sel.value : '';
        if (val === '__custom__') {
            var customInput = row.querySelector('.webhook-form-header-custom-name');
            return customInput ? customInput.value.trim() : '';
        }
        return val || '';
    }

    function buildHeadersFromTable(tbody) {
        var out = {};
        if (!tbody) return out;
        [].slice.call(tbody.querySelectorAll('tr')).forEach(function (row) {
            var name = getHeaderNameFromRow(row);
            var valInput = row.querySelector('.webhook-form-header-value');
            var val = valInput ? valInput.value.trim() : '';
            if (name) out[name] = val;
        });
        return out;
    }

    function buildBodyFromTable(tbody) {
        var obj = {};
        if (!tbody) return '';
        [].slice.call(tbody.querySelectorAll('tr')).forEach(function (row) {
            var keyInput = row.querySelector('.webhook-form-body-key');
            var valInput = row.querySelector('.webhook-form-body-value');
            var key = keyInput ? keyInput.value.trim() : '';
            var val = valInput ? valInput.value : '';
            if (key) obj[key] = val;
        });
        return Object.keys(obj).length === 0 ? '' : JSON.stringify(obj);
    }

    function addHeaderRow(tbody, valueName, valuePlaceholder) {
        valueName = valueName || 'Content-Type';
        valuePlaceholder = valuePlaceholder != null ? valuePlaceholder : 'application/json';
        var tr = document.createElement('tr');
        var optHtml = PRESET_HEADERS.map(function (h) {
            var label = h.label || h.name;
            return '<option value="' + (h.name === '__custom__' ? '__custom__' : h.name) + '">' + label + '</option>';
        }).join('');
        tr.innerHTML =
            '<td><select class="webhook-form-header-select">' + optHtml + '</select>' +
            '<input type="text" class="webhook-form-header-custom-name" placeholder="Header name" style="display:none; margin-top: 4px;" /></td>' +
            '<td><input type="text" class="webhook-form-header-value" placeholder="' + (valuePlaceholder || '') + '" /></td>' +
            '<td class="table-cell-actions"><button type="button" class="btn btn-ghost btn-sm webhook-form-remove-row" aria-label="Remove">×</button></td>';
        var sel = tr.querySelector('.webhook-form-header-select');
        var customInput = tr.querySelector('.webhook-form-header-custom-name');
        sel.value = valueName === '__custom__' ? '__custom__' : valueName;
        if (valueName === '__custom__') customInput.style.display = 'block';
        sel.addEventListener('change', function () {
            customInput.style.display = sel.value === '__custom__' ? 'block' : 'none';
        });
        var ph = PRESET_HEADERS.find(function (h) { return h.name === (valueName === '__custom__' ? '__custom__' : valueName); });
        if (ph && ph.placeholder) tr.querySelector('.webhook-form-header-value').placeholder = ph.placeholder;
        tr.querySelector('.webhook-form-remove-row').addEventListener('click', function () { tr.remove(); });
        tbody.appendChild(tr);
    }

    function addBodyRow(tbody, key, value) {
        key = key != null ? key : '';
        value = value != null ? value : '';
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td><input type="text" class="webhook-form-body-key" placeholder="Key" /></td>' +
            '<td><input type="text" class="webhook-form-body-value" placeholder="Value" /></td>' +
            '<td class="table-cell-actions"><button type="button" class="btn btn-ghost btn-sm webhook-form-remove-row" aria-label="Remove">×</button></td>';
        tr.querySelector('.webhook-form-body-key').value = key;
        tr.querySelector('.webhook-form-body-value').value = value;
        tr.querySelector('.webhook-form-remove-row').addEventListener('click', function () { tr.remove(); });
        tbody.appendChild(tr);
    }

    function parseAndFillHeadersTable(tbody, jsonStr) {
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!jsonStr || !jsonStr.trim()) {
            addHeaderRow(tbody, 'Content-Type', 'application/json');
            return;
        }
        try {
            var obj = typeof jsonStr === 'string' ? JSON.parse(jsonStr) : jsonStr;
            if (obj && typeof obj === 'object' && !Array.isArray(obj)) {
                Object.keys(obj).forEach(function (name) {
                    addHeaderRow(tbody, '__custom__', '');
                    var rows = tbody.querySelectorAll('tr');
                    var last = rows[rows.length - 1];
                    if (last) {
                        last.querySelector('.webhook-form-header-custom-name').value = name;
                        last.querySelector('.webhook-form-header-value').value = obj[name];
                    }
                });
                if (tbody.querySelectorAll('tr').length === 0) addHeaderRow(tbody);
            } else {
                addHeaderRow(tbody);
            }
        } catch (_) {
            addHeaderRow(tbody);
        }
    }

    function parseAndFillBodyTable(tbody, jsonStr) {
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!jsonStr || !jsonStr.trim()) {
            addBodyRow(tbody);
            return;
        }
        try {
            var obj = typeof jsonStr === 'string' ? JSON.parse(jsonStr) : jsonStr;
            if (obj && typeof obj === 'object' && !Array.isArray(obj)) {
                Object.keys(obj).forEach(function (key) {
                    addBodyRow(tbody, key, obj[key]);
                });
                if (tbody.querySelectorAll('tr').length === 0) addBodyRow(tbody);
            } else {
                addBodyRow(tbody);
            }
        } catch (_) {
            addBodyRow(tbody);
        }
    }

    function initSection(section) {
        if (section.getAttribute('data-initialized') === 'true') return;
        section.setAttribute('data-initialized', 'true');
        var prefix = section.getAttribute('data-prefix') || '';
        var form = section.closest('form');
        if (!form) return;

        var headersGroup = section.querySelector('.webhook-form-response-headers-group');
        var bodyGroup = section.querySelector('.webhook-form-response-body-group');
        var headersPreset = document.getElementById(prefix + 'response-headers-preset');
        var headersManual = document.getElementById(prefix + 'response-headers-manual');
        var bodyKv = document.getElementById(prefix + 'response-body-kv');
        var bodyManual = document.getElementById(prefix + 'response-body-manual');
        var headersTbody = document.getElementById(prefix + 'response-headers-tbody');
        var bodyTbody = document.getElementById(prefix + 'response-body-tbody');
        var headersTextarea = form.querySelector('[name="response_headers"]');
        var bodyTextarea = form.querySelector('[name="response_body"]');

        if (!headersGroup || !bodyGroup || !headersTbody || !bodyTbody || !headersTextarea || !bodyTextarea) return;

        function isHeadersPreset() {
            var btn = headersGroup.querySelector('.test-webhook-mode-btn.is-active');
            return btn && btn.getAttribute('data-mode') === 'preset';
        }
        function isBodyKv() {
            var btn = bodyGroup.querySelector('.test-webhook-mode-btn.is-active');
            return btn && btn.getAttribute('data-mode') === 'kv';
        }

        headersGroup.querySelectorAll('.test-webhook-mode-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var mode = btn.getAttribute('data-mode');
                headersGroup.querySelectorAll('.test-webhook-mode-btn').forEach(function (b) {
                    b.classList.toggle('is-active', b === btn);
                    b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
                });
                if (mode === 'preset') {
                    headersPreset.hidden = false;
                    headersManual.hidden = true;
                    parseAndFillHeadersTable(headersTbody, headersTextarea.value);
                } else {
                    headersPreset.hidden = true;
                    headersManual.hidden = false;
                }
            });
        });

        bodyGroup.querySelectorAll('.test-webhook-mode-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var mode = btn.getAttribute('data-mode');
                bodyGroup.querySelectorAll('.test-webhook-mode-btn').forEach(function (b) {
                    b.classList.toggle('is-active', b === btn);
                    b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
                });
                if (mode === 'kv') {
                    bodyKv.hidden = false;
                    bodyManual.hidden = true;
                    parseAndFillBodyTable(bodyTbody, bodyTextarea.value);
                } else {
                    bodyKv.hidden = true;
                    bodyManual.hidden = false;
                }
            });
        });

        section.querySelectorAll('.webhook-form-add-header').forEach(function (btn) {
            if (btn.getAttribute('data-prefix') === prefix) {
                btn.addEventListener('click', function () { addHeaderRow(headersTbody); });
            }
        });
        section.querySelectorAll('.webhook-form-add-body-row').forEach(function (btn) {
            if (btn.getAttribute('data-prefix') === prefix) {
                btn.addEventListener('click', function () { addBodyRow(bodyTbody); });
            }
        });

        if (headersTbody.querySelectorAll('tr').length === 0) addHeaderRow(headersTbody, 'Content-Type', 'application/json');
        if (bodyTbody.querySelectorAll('tr').length === 0) addBodyRow(bodyTbody);
        if (headersTextarea.value.trim()) parseAndFillHeadersTable(headersTbody, headersTextarea.value);
        if (bodyTextarea.value.trim()) parseAndFillBodyTable(bodyTbody, bodyTextarea.value);

        form.addEventListener('submit', function () {
            if (isHeadersPreset()) {
                var headersObj = buildHeadersFromTable(headersTbody);
                headersTextarea.value = Object.keys(headersObj).length > 0 ? JSON.stringify(headersObj, null, 2) : '';
            }
            if (isBodyKv()) {
                bodyTextarea.value = buildBodyFromTable(bodyTbody);
            }
        });

        section.refillResponseTables = function () {
            if (isHeadersPreset()) parseAndFillHeadersTable(headersTbody, headersTextarea.value);
            if (isBodyKv()) parseAndFillBodyTable(bodyTbody, bodyTextarea.value);
        };
    }

    function run() {
        document.querySelectorAll('.webhook-form-response-section:not([data-initialized])').forEach(initSection);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
