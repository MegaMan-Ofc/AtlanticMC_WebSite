<section class="cart-panel" id="cart-panel" data-cart-panel>
    <header class="cart-header">
        <div class="cart-title"><?= e(t('cart.your_products')) ?></div>
        <div class="cart-total-header"><?= e(format_money((int) $cart['total_cents'])) ?></div>
    </header>

    <?php if ($cart['items'] === []): ?>
        <div class="empty-cart">
            <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
            <h2><?= e(t('cart.empty_title')) ?></h2>
            <p><?= e(t('cart.empty_text')) ?></p>
            <a class="button button--primary" href="<?= e(route_url('home')) ?>"><?= e(t('cart.browse_store')) ?></a>
        </div>
    <?php else: ?>
        <form
            action="<?= e(url('actions/update_cart.php')) ?>"
            method="post"
            data-ajax-cart
            data-ajax-url="<?= e(url('ajax/cart.php')) ?>"
            data-cart-operation="update"
            data-render-cart="1"
        >
            <?= csrf_field() ?>

            <div class="cart-body">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th><?= e(t('common.image')) ?></th>
                            <th><?= e(t('common.product')) ?></th>
                            <th><?= e(t('common.price')) ?></th>
                            <th><?= e(t('common.quantity')) ?></th>
                            <th><?= e(t('common.total')) ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cart['items'] as $item): ?>
                        <?php $product = localized_product($item['product']); ?>
                        <tr>
                            <td class="cart-item-image">
                                <img src="<?= e(url($item['product']['image'])) ?>" alt="<?= e($product['name']) ?>" loading="lazy" decoding="async">
                            </td>
                            <td>
                                <div class="item-name"><?= e($product['name']) ?></div>
                                <div class="item-type"><?= e(localized_category((string) $item['product']['category'])) ?></div>
                            </td>
                            <td class="price-amount">
                                <?php if (product_has_discount($item['product'])): ?>
                                    <span class="cart-original-price">
                                        <?= e(format_money((int) $item['product']['price_cents'])) ?>
                                    </span>
                                <?php endif; ?>
                                <span class="cart-current-price">
                                    <?= e(format_money(product_effective_price_cents($item['product']))) ?>
                                </span>
                            </td>
                            <td>
                                <div class="quantity-stepper quantity-stepper--compact" data-quantity-stepper data-cart-auto-update>
                                    <button
                                        class="quantity-stepper__button"
                                        type="button"
                                        data-quantity-decrease
                                        aria-label="<?= e(t('cart.quantity_decrease_for', ['product' => $product['name']])) ?>"
                                    >−</button>
                                    <input
                                        class="quantity-stepper__input"
                                        type="number"
                                        min="1"
                                        max="<?= (int) config('app.max_cart_quantity') ?>"
                                        name="quantities[<?= (int) $item['product']['id'] ?>]"
                                        value="<?= (int) $item['quantity'] ?>"
                                        inputmode="numeric"
                                        data-quantity-input
                                        aria-label="<?= e(t('cart.quantity_for', ['product' => $product['name']])) ?>"
                                    >
                                    <button
                                        class="quantity-stepper__button"
                                        type="button"
                                        data-quantity-increase
                                        aria-label="<?= e(t('cart.quantity_increase_for', ['product' => $product['name']])) ?>"
                                    >+</button>
                                </div>
                            </td>
                            <td class="price-amount"><?= e(format_money((int) $item['line_total_cents'])) ?></td>
                            <td>
                                <button
                                    class="btn-icon btn-remove"
                                    type="submit"
                                    formaction="<?= e(url('actions/remove_from_cart.php')) ?>"
                                    name="product_id"
                                    value="<?= (int) $item['product']['id'] ?>"
                                    data-cart-operation="remove"
                                    aria-label="<?= e(t('cart.remove_product', ['product' => $product['name']])) ?>"
                                >
                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-footer cart-footer--info">
                <div class="cart-footer-info">
                    <p><?= e(t('cart.server_prices')) ?></p>
                    <p><?= e(t('cart.vat_included', ['amount' => format_money((int) $cart['vat_included_cents'])])) ?></p>
                </div>
                <noscript><button class="button button--ghost" type="submit"><?= e(t('cart.update')) ?></button></noscript>
            </div>
        </form>

        <div class="cart-coupon">
            <?php if (tebex_is_configured() && !tebex_coupons_enabled()): ?>
                <p class="coupon-message">
                    <?= e(t('tebex.coupons_disabled')) ?>
                </p>
            <?php elseif ($cart['coupon'] === null): ?>
                <form
                    class="coupon-input-group"
                    action="<?= e(url('actions/apply_coupon.php')) ?>"
                    method="post"
                    data-ajax-cart
                    data-ajax-url="<?= e(url('ajax/cart.php')) ?>"
                    data-cart-operation="apply_coupon"
                    data-render-cart="1"
                >
                    <?= csrf_field() ?>
                    <label class="sr-only" for="coupon-code"><?= e(t('cart.coupon')) ?></label>
                    <input class="field" id="coupon-code" name="coupon_code" maxlength="50" placeholder="<?= e(t('cart.coupon_placeholder')) ?>" required>
                    <button class="button button--ghost" type="submit"><?= e(t('cart.apply_coupon')) ?></button>
                </form>
            <?php else: ?>
                <form
                    class="coupon-input-group"
                    action="<?= e(url('actions/remove_coupon.php')) ?>"
                    method="post"
                    data-ajax-cart
                    data-ajax-url="<?= e(url('ajax/cart.php')) ?>"
                    data-cart-operation="remove_coupon"
                    data-render-cart="1"
                >
                    <?= csrf_field() ?>
                    <p class="coupon-message coupon-success">
                        <?= e(t('cart.coupon_applied', ['code' => $cart['coupon']['code'], 'amount' => format_money((int) $cart['discount_cents'])])) ?>
                    </p>
                    <button class="button button--ghost" type="submit"><?= e(t('cart.remove_coupon')) ?></button>
                </form>
            <?php endif; ?>
        </div>

        <div class="cart-footer">
            <a class="button button--ghost" href="<?= e(route_url('home')) ?>"><?= e(t('cart.continue_shopping')) ?></a>
            <a class="button button--primary" href="<?= e(route_url('checkout')) ?>"><?= e(t('cart.checkout', ['amount' => format_money((int) $cart['total_cents'])])) ?></a>
        </div>
    <?php endif; ?>
</section>
