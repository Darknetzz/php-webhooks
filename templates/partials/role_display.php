<?php
// Expects $role (user, admin, superadmin). Outputs icon + label. Keep mapping in one place.
$roleIcons = [
    \App\User::ROLE_USER => ['icon-user', 'User'],
    \App\User::ROLE_ADMIN => ['icon-admin', 'Admin'],
    \App\User::ROLE_SUPERADMIN => ['icon-superadmin', 'Superadmin'],
];
$role = $role ?? '';
$entry = $roleIcons[$role] ?? ['icon-user', ucfirst($role)];
list($iconId, $label) = $entry;
?>
<span class="role-badge">
    <svg class="icon" aria-hidden="true"><use href="#<?= e($iconId) ?>"/></svg>
    <span><?= e($label) ?></span>
</span>
