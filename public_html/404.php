<?php require_once dirname(__DIR__) . '/controllers/not_found.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require dirname(__DIR__) . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require dirname(__DIR__) . '/includes/header.php'; ?>
    <main class="main-content not-found-main" id="main">
        <div class="container">
            <?php require dirname(__DIR__) . '/templates/errors/not-found.php'; ?>
        </div>
    </main>
    <?php require dirname(__DIR__) . '/includes/footer.php'; ?>
</div>
</body>
</html>
