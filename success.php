<?php require_once __DIR__ . '/controllers/success.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require __DIR__ . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require __DIR__ . '/includes/header.php'; ?>
    <main class="main-content" id="main">
        <section class="success-card">
            <?php if ($order === null): ?>
                <div class="success-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                <h1><?= e(t('success.not_found')) ?></h1>
                <p><?= e(t('success.not_found_text')) ?></p>
            <?php else: ?>
                <div class="success-icon"><i class="fa-solid <?= $order['status'] === 'paid' ? 'fa-check' : 'fa-clock' ?>"></i></div>
                <h1><?= e($order['status'] === 'paid' ? t('success.payment_completed') : t('success.order_created')) ?></h1>
                <p><?= e(t('success.status', ['status' => localized_order_status((string) $order['status'])])) ?></p>
                <div class="success-details">
                    <div class="success-detail"><i class="fa-solid fa-hashtag"></i><div><strong><?= e(t('common.order')) ?></strong><span><?= e($order['public_token']) ?></span></div></div>
                    <div class="success-detail"><i class="fa-solid fa-user"></i><div><strong><?= e(t('success.recipient')) ?></strong><span><?= e($order['minecraft_name']) ?> (<?= e(localized_platform((string) $order['minecraft_platform'])) ?>)</span></div></div>
                    <div class="success-detail"><i class="fa-solid fa-euro-sign"></i><div><strong><?= e(t('common.total')) ?></strong><span><?= e(format_money((int) $order['total_cents'], $order['currency'])) ?></span></div></div>
                </div>
                <?php if ($order['status'] !== 'paid'): ?>
                    <div class="success-note"><i class="fa-solid fa-circle-info"></i><span><?= e(t('success.webhook_note')) ?></span></div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="success-actions"><a class="button button--primary" href="<?= e(url('index.php')) ?>"><?= e(t('common.back_to_store')) ?></a><a class="button button--ghost" href="<?= e(config('app.discord_url')) ?>" target="_blank" rel="noopener noreferrer"><?= e(t('common.support')) ?></a></div>
        </section>
    </main>
    <?php require __DIR__ . '/includes/footer.php'; ?>
</div>
</body>
</html>
