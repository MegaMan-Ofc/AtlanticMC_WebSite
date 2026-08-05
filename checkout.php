<?php require_once __DIR__ . '/controllers/checkout.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require __DIR__ . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require __DIR__ . '/includes/header.php'; ?>
    <main class="main-content" id="main">
        <div class="container">
            <header class="page-title"><a aria-label="<?= e(t('common.back')) ?>" href="<?= e(route_url('cart')) ?>"><i class="fa-solid fa-arrow-left"></i></a><h1><?= e(t('checkout.heading')) ?></h1></header>
            <div class="checkout-grid">
                <section class="checkout-card">
                    <header><span>1</span><h2><?= e(t('checkout.confirm_purchase')) ?></h2></header>
                    <div class="checkout-body">
                        <div class="checkout-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <h2><?= e(t('checkout.purchase_for', ['username' => $checkoutRecipient['username']])) ?></h2>
                        <p><?= e(t('checkout.validation_text')) ?></p>
                        <div class="cart-items-preview">
                            <div class="cart-preview-items">
                                <?php foreach ($cart['items'] as $item): ?>
                                    <?php $localizedProduct = localized_product($item['product']); ?>
                                    <div class="cart-preview-item"><span><?= e($localizedProduct['name']) ?> × <?= (int) $item['quantity'] ?></span><strong><?= e(format_money($item['line_total_cents'])) ?></strong></div>
                                <?php endforeach; ?>
                                <div class="cart-preview-total"><span><?= e(t('common.total')) ?></span><strong><?= e(format_money($cart['total_cents'])) ?></strong></div>
                            </div>
                        </div>
                        <form
                            action="<?= e(url('actions/checkout.php')) ?>"
                            method="post"
                            data-ajax-checkout
                            data-ajax-url="<?= e(url('ajax/checkout.php')) ?>"
                        >
                            <?= csrf_field() ?>
                            <button class="button button--primary button--large button--wide" type="submit">
                                <i class="fa-solid fa-lock"></i>
                                <?= e(tebex_is_configured() ? t('checkout.continue_payment') : ((bool) config('app.allow_test_orders') ? t('checkout.create_test_order') : t('checkout.payment_unavailable'))) ?>
                            </button>
                        </form>
                        <div class="checkout-features">
                            <div class="checkout-feature"><i class="fa-solid fa-user-shield"></i><span><?= e(t('checkout.recipient_selected')) ?></span></div>
                            <div class="checkout-feature"><i class="fa-solid fa-database"></i><span><?= e(t('checkout.server_prices')) ?></span></div>
                            <div class="checkout-feature"><i class="fa-solid fa-receipt"></i><span><?= e(t('checkout.order_recorded')) ?></span></div>
                        </div>
                        <?php if (!tebex_is_configured()): ?>
                            <div class="checkout-note"><i class="fa-solid fa-circle-info"></i><span><?= e((bool) config('app.allow_test_orders') ? t('checkout.test_mode') : t('checkout.tebex_unavailable')) ?></span></div>
                        <?php endif; ?>
                    </div>
                </section>
                <aside class="checkout-summary">
                    <div class="summary-card">
                        <h2><?= e(t('checkout.order_summary')) ?></h2>
                        <div class="summary-items">
                            <?php foreach ($cart['items'] as $item): ?>
                                <?php $localizedProduct = localized_product($item['product']); ?>
                                <div class="summary-item">
                                    <div class="summary-item-info"><img src="<?= e(url($item['product']['image'])) ?>" alt=""><div><div class="summary-item-name"><?= e($localizedProduct['name']) ?></div><div class="summary-item-qty"><?= e(t('checkout.quantity', ['quantity' => (int) $item['quantity']])) ?></div></div></div>
                                    <div class="summary-item-price"><?= e(format_money($item['line_total_cents'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-row"><span><?= e(t('common.subtotal')) ?></span><span><?= e(format_money($cart['subtotal_cents'])) ?></span></div>
                        <div class="summary-row"><span><?= e(t('common.discount')) ?></span><span>-<?= e(format_money($cart['discount_cents'])) ?></span></div>
                        <div class="summary-row"><span><?= e(t('checkout.vat_included')) ?></span><span><?= e(format_money($cart['vat_included_cents'])) ?></span></div>
                        <div class="summary-row summary-total"><span><?= e(t('common.total')) ?></span><span><?= e(format_money($cart['total_cents'])) ?></span></div>
                    </div>
                </aside>
            </div>
        </div>
    </main>
    <?php require __DIR__ . '/includes/footer.php'; ?>
</div>
</body>
</html>
