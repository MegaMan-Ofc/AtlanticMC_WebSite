<?php require_once __DIR__ . '/controllers/rubis.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require __DIR__ . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require __DIR__ . '/includes/header.php'; ?>
    <?php require __DIR__ . '/templates/catalog.php'; ?>
    <?php require __DIR__ . '/includes/footer.php'; ?>
</div>
</body>
</html>
