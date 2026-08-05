<section class="cart-panel" id="cart-panel" data-cart-panel>
    <header class="cart-header">
        <div class="cart-title">Your products</div>
        <div class="cart-total-header"><?= e(format_money((int) $cart['total_cents'])) ?></div>
    </header>

    <?php if ($cart['items'] === []): ?>
        <div class="empty-cart">
            <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
            <h2>Your cart is empty</h2>
            <p>Add products from one of the store categories.</p>
            <a class="button button--primary" href="<?= e(url('index.php')) ?>">Browse store</a>
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
                            <th>Image</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cart['items'] as $item): ?>
                        <?php $product = $item['product']; ?>
                        <tr>
                            <td class="cart-item-image">
                                <img src="<?= e(url($product['image'])) ?>" alt="<?= e($product['name']) ?>">
                            </td>
                            <td>
                                <div class="item-name"><?= e($product['name']) ?></div>
                                <div class="item-type"><?= e(ucfirst($product['category'])) ?></div>
                            </td>
                            <td class="price-amount"><?= e(format_money((int) $product['price_cents'])) ?></td>
                            <td>
                                <input
                                    class="qty-input"
                                    type="number"
                                    min="0"
                                    max="<?= (int) config('app.max_cart_quantity') ?>"
                                    name="quantities[<?= (int) $product['id'] ?>]"
                                    value="<?= (int) $item['quantity'] ?>"
                                    aria-label="Quantity for <?= e($product['name']) ?>"
                                >
                            </td>
                            <td class="price-amount"><?= e(format_money((int) $item['line_total_cents'])) ?></td>
                            <td>
                                <button
                                    class="btn-icon btn-remove"
                                    type="submit"
                                    formaction="<?= e(url('actions/remove_from_cart.php')) ?>"
                                    name="product_id"
                                    value="<?= (int) $product['id'] ?>"
                                    data-cart-operation="remove"
                                    aria-label="Remove <?= e($product['name']) ?>"
                                >
                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-footer">
                <div class="cart-footer-info">
                    <p>Prices are calculated again on the server before checkout.</p>
                    <p>IVA included: <?= e(format_money((int) $cart['vat_included_cents'])) ?></p>
                </div>
                <button class="button button--ghost" type="submit">Update cart</button>
            </div>
        </form>

        <div class="cart-coupon">
            <?php if ($cart['coupon'] === null): ?>
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
                    <label class="sr-only" for="coupon-code">Coupon</label>
                    <input class="field" id="coupon-code" name="coupon_code" maxlength="50" placeholder="Coupon code" required>
                    <button class="button button--ghost" type="submit">Apply coupon</button>
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
                        Coupon <?= e($cart['coupon']['code']) ?>: -<?= e(format_money((int) $cart['discount_cents'])) ?>
                    </p>
                    <button class="button button--ghost" type="submit">Remove</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="cart-footer">
            <a class="button button--ghost" href="<?= e(url('index.php')) ?>">Continue shopping</a>
            <a class="button button--primary" href="<?= e(url('checkout.php')) ?>">
                Checkout · <?= e(format_money((int) $cart['total_cents'])) ?>
            </a>
        </div>
    <?php endif; ?>
</section>
