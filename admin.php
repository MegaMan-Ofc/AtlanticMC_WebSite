<?php require_once __DIR__ . '/controllers/admin.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require __DIR__ . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<?php require __DIR__ . '/templates/admin/page.php'; ?>
<script defer src="<?= e(url('js/admin.js')) ?>"></script>
<?php if (!$adminAuthenticated): ?>
    <script defer src="<?= e(url('js/password-toggle.js')) ?>"></script>
<?php endif; ?>
</body>
</html>
