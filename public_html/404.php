<?php require_once dirname(__DIR__) . '/controllers/Site/not_found.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require dirname(__DIR__) . '/templates/layout/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require dirname(__DIR__) . '/templates/layout/header.php'; ?>
    <main class="main-content not-found-main" id="main">
        <div class="container">
            <?php require dirname(__DIR__) . '/templates/errors/not-found.php'; ?>
        </div>
    </main>
    <?php require dirname(__DIR__) . '/templates/layout/footer.php'; ?>
</div>
</body>
</html>
