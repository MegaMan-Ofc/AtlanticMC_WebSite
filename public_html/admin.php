<?php require_once dirname(__DIR__) . '/controllers/admin.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require dirname(__DIR__) . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<?php require dirname(__DIR__) . '/templates/admin/page.php'; ?>
<script defer src="<?= e(url('js/admin.js')) ?>"></script>
<script defer src="<?= e(url('js/password-toggle.js')) ?>"></script>
</body>
</html>
