<?php require_once dirname(__DIR__) . '/controllers/Store/cart.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require dirname(__DIR__) . '/templates/layout/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require dirname(__DIR__) . '/templates/layout/header.php'; ?>
    <main class="main-content" id="main">
        <div class="container">
            <header class="page-title">
                <a aria-label="<?= e(t('common.back')) ?>" href="<?= e(route_url('home')) ?>">
                    <i class="fa-solid fa-house" aria-hidden="true"></i>
                </a>
                <h1><?= e(t('cart.heading')) ?></h1>
            </header>

            <?php require dirname(__DIR__) . '/templates/store/cart.php'; ?>
        </div>
    </main>
    <?php require dirname(__DIR__) . '/templates/layout/footer.php'; ?>
</div>
</body>
</html>
