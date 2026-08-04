<?php require_once __DIR__ . '/controllers/success.php'; ?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require __DIR__ . '/includes/header.php'; ?>
    <main class="main-content" id="main">
        <section class="success-card">
            <?php if ($order === null): ?>
                <div class="success-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                <h1>Order not found</h1>
                <p>The order is unavailable or the order token is invalid.</p>
            <?php else: ?>
                <div class="success-icon"><i class="fa-solid <?= $order['status'] === 'paid' ? 'fa-check' : 'fa-clock' ?>"></i></div>
                <h1><?= $order['status'] === 'paid' ? 'Payment completed' : 'Order created' ?></h1>
                <p>Status: <strong><?= e(str_replace('_', ' ', ucfirst($order['status']))) ?></strong></p>
                <div class="success-details">
                    <div class="success-detail"><i class="fa-solid fa-hashtag"></i><div><strong>Order</strong><span><?= e($order['public_token']) ?></span></div></div>
                    <div class="success-detail"><i class="fa-solid fa-user"></i><div><strong>Minecraft recipient</strong><span><?= e($order['minecraft_name']) ?> (<?= e(ucfirst($order['minecraft_platform'])) ?>)</span></div></div>
                    <div class="success-detail"><i class="fa-solid fa-euro-sign"></i><div><strong>Total</strong><span><?= e(format_money((int) $order['total_cents'], $order['currency'])) ?></span></div></div>
                </div>
                <?php if ($order['status'] !== 'paid'): ?>
                    <div class="success-note"><i class="fa-solid fa-circle-info"></i><span>An order is only considered paid after a verified payment webhook changes its status.</span></div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="success-actions"><a class="button button--primary" href="<?= e(url('index.php')) ?>">Back to store</a><a class="button button--ghost" href="<?= e(config('app.discord_url')) ?>" target="_blank" rel="noopener noreferrer">Support</a></div>
        </section>
    </main>
    <?php require __DIR__ . '/includes/footer.php'; ?>
</div>
</body>
</html>
