<?php $legalPageKey = 'terms'; require_once dirname(__DIR__) . '/controllers/Site/legal.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require dirname(__DIR__) . '/templates/layout/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require dirname(__DIR__) . '/templates/layout/header.php'; ?>
    <main class="main-content" id="main">
        <div class="container">
            <?php require dirname(__DIR__) . '/templates/site/legal.php'; ?>
        </div>
    </main>
    <?php require dirname(__DIR__) . '/templates/layout/footer.php'; ?>
</div>
</body>
</html>
