<?php require_once __DIR__ . '/controllers/checkout.php'; ?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require __DIR__ . '/includes/header.php'; ?>
    <main class="main-content" id="main">
        <div class="container">
            <header class="page-title"><a aria-label="Go back" href="<?= e(url('cart.php')) ?>"><i class="fa-solid fa-arrow-left"></i></a><h1>Checkout</h1></header>
            <div class="checkout-grid">
                <section class="checkout-card">
                    <header><span>1</span><h2>Confirm the purchase</h2></header>
                    <div class="checkout-body">
                        <div class="checkout-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <h2>Purchase for <?= e($checkoutRecipient['username']) ?></h2>
                        <p>The server validates the recipient name, platform, product IDs, quantities, coupon and prices before creating the order.</p>
                        <div class="cart-items-preview">
                            <div class="cart-preview-items">
                                <?php foreach ($cart['items'] as $item): ?>
                                    <div class="cart-preview-item"><span><?= e($item['product']['name']) ?> × <?= (int) $item['quantity'] ?></span><strong><?= e(format_money($item['line_total_cents'])) ?></strong></div>
                                <?php endforeach; ?>
                                <div class="cart-preview-total"><span>Total</span><strong><?= e(format_money($cart['total_cents'])) ?></strong></div>
                            </div>
                        </div>
                        <form action="<?= e(url('actions/checkout.php')) ?>" method="post">
                            <?= csrf_field() ?>
                            <button class="button button--primary button--large button--wide" type="submit">
                                <i class="fa-solid fa-lock"></i>
                                <?= tebex_is_configured() ? 'Continue to secure payment' : ((bool) config('app.allow_test_orders') ? 'Create test order' : 'Payment unavailable') ?>
                            </button>
                        </form>
                        <div class="checkout-features">
                            <div class="checkout-feature"><i class="fa-solid fa-user-shield"></i><span>Minecraft recipient selected</span></div>
                            <div class="checkout-feature"><i class="fa-solid fa-database"></i><span>Server-side prices</span></div>
                            <div class="checkout-feature"><i class="fa-solid fa-receipt"></i><span>Order recorded</span></div>
                        </div>
                        <?php if (!tebex_is_configured()): ?>
                            <div class="checkout-note"><i class="fa-solid fa-circle-info"></i><span><?= (bool) config('app.allow_test_orders') ? 'Test mode is active. This creates a local pending order without charging money.' : 'Payments are temporarily unavailable until Tebex is configured.' ?></span></div>
                        <?php endif; ?>
                    </div>
                </section>
                <aside class="checkout-summary">
                    <div class="summary-card">
                        <h2>Order summary</h2>
                        <div class="summary-items">
                            <?php foreach ($cart['items'] as $item): ?>
                                <div class="summary-item">
                                    <div class="summary-item-info"><img src="<?= e(url($item['product']['image'])) ?>" alt=""><div><div class="summary-item-name"><?= e($item['product']['name']) ?></div><div class="summary-item-qty">Quantity: <?= (int) $item['quantity'] ?></div></div></div>
                                    <div class="summary-item-price"><?= e(format_money($item['line_total_cents'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-row"><span>Subtotal</span><span><?= e(format_money($cart['subtotal_cents'])) ?></span></div>
                        <div class="summary-row"><span>Discount</span><span>-<?= e(format_money($cart['discount_cents'])) ?></span></div>
                        <div class="summary-row"><span>IVA included</span><span><?= e(format_money($cart['vat_included_cents'])) ?></span></div>
                        <div class="summary-row summary-total"><span>Total</span><span><?= e(format_money($cart['total_cents'])) ?></span></div>
                    </div>
                </aside>
            </div>
        </div>
    </main>
    <?php require __DIR__ . '/includes/footer.php'; ?>
</div>
</body>
</html>
