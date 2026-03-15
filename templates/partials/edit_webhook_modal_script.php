<?php
// Script for edit webhook modal. Expects $baseUrl in scope. Works with .webhook-card or .webhook-row (tr) with data-id, data-name, etc.
if (!isset($baseUrl)) {
    $baseUrl = rtrim(base_url(), '/');
}
?>
<script>
(function () {
    var baseUrl = <?= json_encode($baseUrl) ?>;
    var modal = document.getElementById('edit-modal');
    if (!modal) return;
    function openModal(id) {
        var el = document.getElementById(id);
        if (el) {
            el.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
    }
    function closeModal(id) {
        var el = document.getElementById(id);
        if (el) {
            el.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    }
    modal.querySelectorAll('[data-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            closeModal(this.getAttribute('data-close'));
        });
    });
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal('edit-modal');
    });
    modal.querySelector('.modal').addEventListener('click', function (e) { e.stopPropagation(); });

    document.querySelectorAll('.btn-edit-webhook').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = this.closest('.webhook-card') || this.closest('.webhook-row');
            if (!row || !row.dataset.id) return;
            var d = row.dataset;
            document.getElementById('edit-name').value = d.name || '';
            document.getElementById('edit-slug').value = d.slug || '';
            document.getElementById('edit-description').value = d.description || '';
            document.getElementById('edit-is_public').checked = d.isPublic === '1';
            document.getElementById('edit-requests_public').checked = d.requestsPublic === '1';
            document.getElementById('edit-is_public').dispatchEvent(new Event('change'));
            document.getElementById('edit-response_status_code').value = d.responseStatusCode || '200';
            document.getElementById('edit-response_headers').value = d.responseHeaders || '';
            document.getElementById('edit-response_body').value = d.responseBody || '';
            var responseSection = document.querySelector('#edit-modal .webhook-form-response-section');
            if (responseSection && responseSection.refillResponseTables) responseSection.refillResponseTables();
            document.getElementById('edit-slug-preview').textContent = d.slug || '';
            var allowed = (d.allowedMethods || '').split(',').map(function (m) { return m.trim(); }).filter(Boolean);
            document.querySelectorAll('#edit-modal input[name="allowed_methods[]"]').forEach(function (cb) {
                cb.checked = allowed.indexOf(cb.value) !== -1;
            });
            var specifyToggle = document.getElementById('edit-specify-allowed-methods');
            if (specifyToggle) {
                specifyToggle.checked = allowed.length > 0;
                specifyToggle.dispatchEvent(new Event('change'));
            }
            document.getElementById('edit-webhook-form').action = baseUrl + '/admin/webhooks/' + d.id + '/edit';
            openModal('edit-modal');
        });
    });

    var slugInput = document.getElementById('edit-slug');
    if (slugInput) {
        slugInput.addEventListener('input', function () {
            var preview = document.getElementById('edit-slug-preview');
            if (preview) preview.textContent = this.value || '';
        });
    }
})();
</script>
