<main class="main-content" id="main">
    <div class="container">
        <header class="page-title">
            <a aria-label="<?= e(t('catalog.go_back')) ?>" href="<?= e(route_url('home')) ?>"><i class="fa-solid fa-house" aria-hidden="true"></i></a>
            <div>
                <h1 id="page-title"><?= e($pageHeading) ?></h1>
                <p class="page-subtitle"><?= e($pageDescription) ?></p>
            </div>
        </header>

        <?php if ($products === []): ?>
            <section class="prose" aria-labelledby="page-title">
                <h2><?= e(t('catalog.no_products')) ?></h2>
                <p><?= e(t('catalog.no_products_text')) ?></p>
            </section>
        <?php else: ?>
            <section class="catalog-grid" aria-labelledby="page-title">
                <?php foreach ($products as $product): ?>
                    <?php
                        $localizedProduct = localized_product($product);
                        $metadata = localized_product_metadata($product);
                        $theme = (string) ($metadata['theme'] ?? '');
                        $cardClasses = ['package'];
                        if ($category === 'keys' && $theme !== '') {
                            $cardClasses[] = 'package-' . $theme . '-card';
                        }
                    ?>
                    <article class="<?= e(implode(' ', $cardClasses)) ?>"<?php if (isset($metadata['color'])): ?> style="--rank-color: <?= e($metadata['color']) ?>; --booster-color: <?= e($metadata['color']) ?>"<?php endif; ?>>
                        <?php if (product_has_discount($product)): ?>
                            <span class="package-discount-badge"><?= e(t('common.discount')) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($metadata['badge'])): ?>
                            <span class="package-bonus-badge<?= product_has_discount($product) ? ' package-bonus-badge--stacked' : '' ?>"><?= e($metadata['badge']) ?></span>
                        <?php endif; ?>
                        <div class="image">
                            <div class="package-image-wrap">
                                <img class="package-image-glow" src="<?= e(url($product['image'])) ?>" alt="" aria-hidden="true">
                                <img class="package-image-main" src="<?= e(url($product['image'])) ?>" alt="<?= e($localizedProduct['name']) ?>">
                            </div>
                        </div>
                        <div class="info">
                            <h2 class="name"><?= e($localizedProduct['name']) ?></h2>
                            <?php if (!empty($metadata['amount'])): ?>
                                <div class="rubis-amount"><?= e($metadata['amount']) ?></div>
                            <?php endif; ?>
                            <p class="booster-description"><?= e($localizedProduct['description']) ?></p>
                            <?php if (!empty($metadata['features']) && is_array($metadata['features'])): ?>
                                <div class="package-benefits">
                                    <?php foreach ($metadata['features'] as $feature): ?>
                                        <div class="benefit-item"><i class="fa-solid fa-check" aria-hidden="true"></i> <?= e($feature) ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="price">
                                <?php if (product_has_discount($product)): ?>
                                    <span class="discount">
                                        <?= e(format_money((int) $product['price_cents'], $product['currency'])) ?>
                                    </span>
                                <?php endif; ?>
                                <span class="package-active-price">
                                    <?= e(format_money(product_effective_price_cents($product), $product['currency'])) ?>
                                </span>
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
                                <input type="hidden" name="return_to" value="<?= e(current_public_return_path()) ?>">
                                <label class="sr-only" for="quantity-<?= (int) $product['id'] ?>"><?= e(t('catalog.quantity')) ?></label>
                                <select class="field product-quantity" id="quantity-<?= (int) $product['id'] ?>" name="quantity">
                                    <?php for ($quantity = 1; $quantity <= min(5, (int) config('app.max_cart_quantity')); $quantity++): ?>
                                        <option value="<?= $quantity ?>"><?= $quantity ?>×</option>
                                    <?php endfor; ?>
                                </select>
                                <button class="button button--primary button--wide" type="submit">
                                    <i class="fa-solid fa-cart-plus" aria-hidden="true"></i>
                                    <?= e(t('catalog.add_to_cart')) ?>
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </div>
</main>
