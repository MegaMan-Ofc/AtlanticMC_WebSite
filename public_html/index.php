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
                        <div class="home-section-title-box">
                            <span class="home-section-kicker"><?= e(t('home.recommended_kicker')) ?></span>
                            <h2 id="home-recommended-title"><?= e(t('home.recommended_title')) ?></h2>
                        </div>
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

                <div class="home-section-divider" aria-hidden="true">
                    <span></span>
                </div>
            <?php endif; ?>

            <header class="home-section-heading home-store-heading">
                <div class="home-section-title-box home-store-title-box">
                    <span class="home-section-kicker"><?= e(t('home.store_kicker')) ?></span>
                    <h2><?= e(t('home.store_title')) ?></h2>
                </div>
            </header>

            <?php if (!$homeHasCategories): ?>
                <section class="prose home-empty-categories" aria-labelledby="home-categories-title">
                    <h2 id="home-categories-title"><?= e(t('home.no_categories')) ?></h2>
                    <p><?= e(t('home.no_categories_text')) ?></p>
                </section>
            <?php else: ?>
                <?php if ($homeTopCategory !== null): ?>
                    <?php
                    $homeBannerCategory = $homeTopCategory;
                    $homeBannerPosition = 'top';
                    require dirname(__DIR__) . '/templates/home/category-banner.php';
                    ?>
                <?php endif; ?>

                <?php if ($homeCategories !== []): ?>
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

                <?php if ($homeBottomCategory !== null): ?>
                    <?php
                    $homeBannerCategory = $homeBottomCategory;
                    $homeBannerPosition = 'bottom';
                    require dirname(__DIR__) . '/templates/home/category-banner.php';
                    ?>
                <?php endif; ?>
            <?php endif; ?>

            <div class="home-section-divider" aria-hidden="true">
                <span></span>
            </div>

            <section class="home-about" aria-labelledby="home-about-title">
                <header class="home-section-heading home-about-heading">
                    <div class="home-section-title-box home-about-title-box">
                        <span class="home-section-kicker"><?= e(t('home.about_kicker')) ?></span>
                        <h2 id="home-about-title"><?= e(t('home.about_title')) ?></h2>
                    </div>
                </header>

                <div class="home-about-grid">
                    <article class="home-about-copy home-about-copy--primary">
                        <div class="home-about-copy-icon" aria-hidden="true">
                            <i class="fa-solid fa-people-group"></i>
                        </div>
                        <h3><?= e(t('home.about_community_title')) ?></h3>
                        <p><?= e(t('home.about_community_text')) ?></p>
                        <div class="home-about-tags" aria-label="<?= e(t('home.about_highlights_aria')) ?>">
                            <span><i class="fa-solid fa-users" aria-hidden="true"></i><?= e(t('home.about_tag_community')) ?></span>
                        </div>
                        <div class="home-about-faq">
                            <span><?= e(t('home.about_faq_prompt')) ?></span>
                            <a class="button button--ghost home-about-faq-button" href="<?= e(route_url('faq')) ?>">
                                <i class="fa-solid fa-circle-question" aria-hidden="true"></i>
                                <?= e(t('common.faq')) ?>
                            </a>
                        </div>
                    </article>

                    <figure class="home-about-visual home-about-visual--brand">
                        <img src="<?= e(url('assets/logo atlantic.png')) ?>" alt="<?= e(t('home.about_brand_alt')) ?>" loading="lazy">
                    </figure>

                    <article class="home-about-copy home-about-copy--secondary">
                        <div class="home-about-copy-icon" aria-hidden="true">
                            <i class="fa-solid fa-compass"></i>
                        </div>
                        <h3><?= e(t('home.about_adventure_title')) ?></h3>
                        <p><?= e(t('home.about_adventure_text')) ?></p>
                        <div class="home-about-tags" aria-label="<?= e(t('home.about_highlights_aria')) ?>">
                            <span><i class="fa-solid fa-gamepad" aria-hidden="true"></i><?= e(t('home.about_tag_crossplay')) ?></span>
                            <span><i class="fa-solid fa-trophy" aria-hidden="true"></i><?= e(t('home.about_tag_progression')) ?></span>
                        </div>
                    </article>

                    <figure class="home-about-visual home-about-visual--player">
                        <div class="home-about-player-glow" aria-hidden="true"></div>
                        <img src="<?= e(url('assets/steve.png')) ?>" alt="<?= e(t('home.about_player_alt')) ?>" loading="lazy">
                    </figure>
                </div>
            </section>
        </div>
    </main>
    <?php require dirname(__DIR__) . '/includes/footer.php'; ?>
</div>
</body>
</html>
