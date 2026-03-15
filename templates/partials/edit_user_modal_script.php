<?php
if (!isset($baseUrl)) {
    $baseUrl = rtrim(base_url(), '/');
}
?>
<script>
(function () {
    var baseUrl = <?= json_encode($baseUrl) ?>;
    var modal = document.getElementById('edit-user-modal');
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
    modal.querySelectorAll('[data-close="edit-user-modal"]').forEach(function (btn) {
        btn.addEventListener('click', function () { closeModal('edit-user-modal'); });
    });
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal('edit-user-modal');
    });

    document.querySelectorAll('.btn-edit-user').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = this.closest('tr');
            if (!row || !row.dataset.userId) return;
            var d = row.dataset;
            document.getElementById('edit-username').value = d.username || '';
            document.getElementById('edit-role').value = d.role || '';
            document.getElementById('edit-password').value = '';
            document.getElementById('edit-user-form').action = baseUrl + '/admin/users/' + d.userId + '/edit';
            openModal('edit-user-modal');
        });
    });
    <?php if (!empty($editUser)): ?>
    document.getElementById('edit-user-form').action = baseUrl + '/admin/users/' + <?= (int) $editUser->id ?> + '/edit';
    openModal('edit-user-modal');
    <?php endif; ?>
})();
</script>
