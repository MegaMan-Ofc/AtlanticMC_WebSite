<?php $category = 'rubis'; require_once dirname(__DIR__) . '/controllers/Store/catalog.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require dirname(__DIR__) . '/templates/layout/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require dirname(__DIR__) . '/templates/layout/header.php'; ?>
    <?php require dirname(__DIR__) . '/templates/store/catalog.php'; ?>
    <?php require dirname(__DIR__) . '/templates/layout/footer.php'; ?>
</div>
</body>
</html>
