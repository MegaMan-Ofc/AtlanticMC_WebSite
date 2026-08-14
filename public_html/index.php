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
            <?php if ($homeHeroCategory !== null): ?>
                <a class="home-hero" href="<?= e(category_url((string) $homeHeroCategory['slug'])) ?>">
                    <img alt="<?= e(t('home.hero_alt')) ?>" src="<?= e(url('assets/magma-key.png')) ?>">
                    <div><strong><?= e(t('home.order_now')) ?></strong><small><?= e(t('home.server_cart')) ?></small></div>
                </a>
            <?php endif; ?>

            <?php if ($homeCategories === []): ?>
                <section class="prose home-empty-categories" aria-labelledby="home-categories-title">
                    <h2 id="home-categories-title"><?= e(t('home.no_categories')) ?></h2>
                    <p><?= e(t('home.no_categories_text')) ?></p>
                </section>
            <?php else: ?>
                <section aria-label="<?= e(t('home.categories_aria')) ?>" class="category-grid">
                    <?php foreach ($homeCategories as $category): ?>
                        <a
                            class="category-card"
                            data-theme="<?= e($category['theme']) ?>"
                            href="<?= e($category['url']) ?>"
                        >
                            <img
                                alt="<?= e(t('home.category_image_alt', ['category' => $category['name']])) ?>"
                                src="<?= e(url($category['image'])) ?>"
                            >
                            <h2><?= e($category['name']) ?></h2>
                        </a>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </div>
    </main>
    <?php require dirname(__DIR__) . '/includes/footer.php'; ?>
</div>
</body>
</html>
