<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/controllers/Store/search.php';
?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require dirname(__DIR__) . '/templates/layout/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require dirname(__DIR__) . '/templates/layout/header.php'; ?>
    <?php require dirname(__DIR__) . '/templates/store/search.php'; ?>
    <?php require dirname(__DIR__) . '/templates/layout/footer.php'; ?>
</div>
</body>
</html>
