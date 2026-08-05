<?php require_once __DIR__ . '/controllers/privacy.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require __DIR__ . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require __DIR__ . '/includes/header.php'; ?>
    <main class="main-content" id="main">
        <div class="container">
            <?php require __DIR__ . '/templates/legal.php'; ?>
        </div>
    </main>
    <?php require __DIR__ . '/includes/footer.php'; ?>
</div>
</body>
</html>
