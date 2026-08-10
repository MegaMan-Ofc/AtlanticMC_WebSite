<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/controllers/public_router.php';
require_once dirname(__DIR__) . '/controllers/index.php';
?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require dirname(__DIR__) . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require dirname(__DIR__) . '/includes/header.php'; ?>
    <main class="main-content" id="main">
        <div class="container">
            <h1 class="sr-only"><?= e(t('home.title')) ?></h1>
            <a class="home-hero" href="<?= e(route_url('keys')) ?>">
                <img alt="<?= e(t('home.hero_alt')) ?>" src="<?= e(url('assets/magma-key.png')) ?>">
                <div><strong><?= e(t('home.order_now')) ?></strong><small><?= e(t('home.server_cart')) ?></small></div>
            </a>
            <section aria-label="<?= e(t('home.categories_aria')) ?>" class="category-grid">
                <?php foreach ($homeCategories as $category): ?>
                    <a
                        class="category-card"
                        data-theme="<?= e($category['theme']) ?>"
                        href="<?= e(route_url($category['route'])) ?>"
                    >
                        <img
                            alt="<?= e(t('home.category_image_alt', ['category' => $category['name']])) ?>"
                            src="<?= e(url($category['image'])) ?>"
                        >
                        <h2><?= e($category['name']) ?></h2>
                    </a>
                <?php endforeach; ?>
            </section>
            <a class="promo-card sdz-rank-card" href="<?= e(route_url('boosters')) ?>">
                <div><p><?= e(t('home.power_up')) ?></p><h2><?= e(t('home.hearts')) ?><br><small><?= e(t('home.extra_life')) ?></small></h2><span class="button button--ghost"><?= e(t('home.view_hearts')) ?></span></div>
                <img alt="<?= e(t('home.heart_alt')) ?>" src="<?= e(url('assets/heart%20(2).png')) ?>">
            </a>
        </div>
    </main>
    <?php require dirname(__DIR__) . '/includes/footer.php'; ?>
</div>
</body>
</html>
