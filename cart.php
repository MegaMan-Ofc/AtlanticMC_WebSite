<?php require_once __DIR__ . '/controllers/cart.php'; ?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require __DIR__ . '/includes/header.php'; ?>
    <main class="main-content" id="main">
        <div class="container">
            <header class="page-title">
                <a aria-label="Go back" href="<?= e(url('index.php')) ?>">
                    <i class="fa-solid fa-house" aria-hidden="true"></i>
                </a>
                <h1>Shopping Cart</h1>
            </header>

            <?php require __DIR__ . '/templates/cart_panel.php'; ?>
        </div>
    </main>
    <?php require __DIR__ . '/includes/footer.php'; ?>
</div>
</body>
</html>
