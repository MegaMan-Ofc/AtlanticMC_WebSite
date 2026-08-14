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

            <?php if ($homeRecommendedProducts !== []): ?>
                <section class="home-recommended" aria-labelledby="home-recommended-title">
                    <header class="home-section-heading">
                        <div>
                            <span class="home-section-kicker"><?= e(t('home.recommended_kicker')) ?></span>
                            <h2 id="home-recommended-title"><?= e(t('home.recommended_title')) ?></h2>
                        </div>
                        <p><?= e(t('home.recommended_text')) ?></p>
                    </header>

                    <div class="home-recommended-grid">
                        <?php foreach ($homeRecommendedProducts as $product): ?>
                            <?php $localizedProduct = localized_product($product); ?>
                            <article class="home-recommended-card">
                                <?php if (product_has_discount($product)): ?>
                                    <span class="home-recommended-discount"><?= e(t('common.discount')) ?></span>
                                <?php endif; ?>

                                <a class="home-recommended-product" href="<?= e(category_url((string) $product['category_slug'])) ?>">
                                    <div class="home-recommended-image">
                                        <img src="<?= e(url((string) $product['image'])) ?>" alt="<?= e((string) $localizedProduct['name']) ?>" loading="lazy">
                                    </div>
                                    <small><?= e((string) $product['category_name']) ?></small>
                                    <h3><?= e((string) $localizedProduct['name']) ?></h3>
                                </a>

                                <div class="home-recommended-price">
                                    <?php if (product_has_discount($product)): ?>
                                        <span><?= e(format_money((int) $product['price_cents'], (string) $product['currency'])) ?></span>
                                    <?php endif; ?>
                                    <strong><?= e(format_money(product_effective_price_cents($product), (string) $product['currency'])) ?></strong>
                                </div>

                                <form
                                    action="<?= e(url('actions/add_to_cart.php')) ?>"
                                    method="post"
                                    data-ajax-cart
                                    data-ajax-url="<?= e(url('ajax/cart.php')) ?>"
                                    data-cart-operation="add"
                                >
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="return_to" value="">
                                    <button class="button button--primary button--wide" type="submit">
                                        <i class="fa-solid fa-cart-plus" aria-hidden="true"></i>
                                        <?= e(t('catalog.add_to_cart')) ?>
                                    </button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

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
