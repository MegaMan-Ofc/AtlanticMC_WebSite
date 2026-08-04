<main class="main-content" id="main">
    <div class="container">
        <header class="page-title">
            <a aria-label="Go back" href="<?= e(url('index.php')) ?>"><i class="fa-solid fa-house" aria-hidden="true"></i></a>
            <div>
                <h1 id="page-title"><?= e($pageHeading) ?></h1>
                <p class="page-subtitle"><?= e($pageDescription) ?></p>
            </div>
        </header>

        <?php if ($products === []): ?>
            <section class="prose" aria-labelledby="page-title">
                <h2>No products available</h2>
                <p>This category currently has no active products.</p>
            </section>
        <?php else: ?>
            <section class="catalog-grid" aria-labelledby="page-title">
                <?php foreach ($products as $product): ?>
                    <?php
                        $metadata = product_metadata($product);
                        $theme = (string) ($metadata['theme'] ?? '');
                        $cardClasses = ['package'];
                        if ($category === 'keys' && $theme !== '') {
                            $cardClasses[] = 'package-' . $theme . '-card';
                        }
                    ?>
                    <article class="<?= e(implode(' ', $cardClasses)) ?>"<?php if (isset($metadata['color'])): ?> style="--rank-color: <?= e($metadata['color']) ?>; --booster-color: <?= e($metadata['color']) ?>"<?php endif; ?>>
                        <?php if (!empty($metadata['badge'])): ?>
                            <span class="package-bonus-badge"><?= e($metadata['badge']) ?></span>
                        <?php endif; ?>
                        <div class="image">
                            <div class="package-image-wrap">
                                <img class="package-image-glow" src="<?= e(url($product['image'])) ?>" alt="" aria-hidden="true">
                                <img class="package-image-main" src="<?= e(url($product['image'])) ?>" alt="<?= e($product['name']) ?>">
                            </div>
                        </div>
                        <div class="info">
                            <h2 class="name"><?= e($product['name']) ?></h2>
                            <?php if (!empty($metadata['amount'])): ?>
                                <div class="rubis-amount"><?= e($metadata['amount']) ?></div>
                            <?php endif; ?>
                            <p class="booster-description"><?= e($product['description']) ?></p>
                            <?php if (!empty($metadata['features']) && is_array($metadata['features'])): ?>
                                <div class="battlepass-benefits">
                                    <?php foreach ($metadata['features'] as $feature): ?>
                                        <div class="benefit-item"><i class="fa-solid fa-check" aria-hidden="true"></i> <?= e($feature) ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="price"><span class="package-active-price"><?= e(format_money((int) $product['price_cents'], $product['currency'])) ?></span></div>
                            <form action="<?= e(url('actions/add_to_cart.php')) ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <input type="hidden" name="return_to" value="<?= e(basename(current_request_path())) ?>">
                                <label class="sr-only" for="quantity-<?= (int) $product['id'] ?>">Quantity</label>
                                <select class="field product-quantity" id="quantity-<?= (int) $product['id'] ?>" name="quantity">
                                    <?php for ($quantity = 1; $quantity <= min(5, (int) config('app.max_cart_quantity')); $quantity++): ?>
                                        <option value="<?= $quantity ?>"><?= $quantity ?>×</option>
                                    <?php endfor; ?>
                                </select>
                                <button class="button button--primary button--wide" type="submit">
                                    <i class="fa-solid fa-cart-plus" aria-hidden="true"></i>
                                    Add to cart
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </div>
</main>
