<?php
$localizedProduct = localized_product($product);
$metadata = localized_product_metadata($product);
$productCategorySlug = (string) ($product['category_slug'] ?? $product['category'] ?? $category ?? '');
$theme = (string) ($metadata['theme'] ?? '');
$cardClasses = ['package'];

if ($productCategorySlug === 'keys' && $theme !== '') {
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
            <img class="package-image-glow" src="<?= e(url((string) $product['image'])) ?>" alt="" aria-hidden="true">
            <img class="package-image-main" src="<?= e(url((string) $product['image'])) ?>" alt="<?= e((string) $localizedProduct['name']) ?>">
        </div>
    </div>
    <div class="info">
        <?php if (isset($product['category_name'])): ?>
            <a class="package-category-link" href="<?= e(category_url($productCategorySlug)) ?>"><?= e((string) $product['category_name']) ?></a>
        <?php endif; ?>
        <h2 class="name"><?= e((string) $localizedProduct['name']) ?></h2>
        <?php if (!empty($metadata['amount'])): ?>
            <div class="rubis-amount"><?= e($metadata['amount']) ?></div>
        <?php endif; ?>
        <p class="booster-description"><?= e((string) $localizedProduct['description']) ?></p>
        <?php if (!empty($metadata['features']) && is_array($metadata['features'])): ?>
            <div class="package-benefits">
                <?php foreach ($metadata['features'] as $feature): ?>
                    <div class="benefit-item"><i class="fa-solid fa-check" aria-hidden="true"></i> <?= e($feature) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="price">
            <?php if (product_has_discount($product)): ?>
                <span class="discount"><?= e(format_money((int) $product['price_cents'], (string) $product['currency'])) ?></span>
            <?php endif; ?>
            <span class="package-active-price"><?= e(format_money(product_effective_price_cents($product), (string) $product['currency'])) ?></span>
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
