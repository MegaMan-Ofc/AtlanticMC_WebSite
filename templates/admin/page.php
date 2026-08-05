<div class="admin-shell">
    <header class="admin-topbar">
        <div>
            <p class="admin-eyebrow">Atlantic Anarchy</p>
            <h1><?= e(t('admin.title')) ?></h1>
            <p><?= e(t('admin.subtitle')) ?></p>
        </div>
        <div class="admin-topbar-actions">
            <a class="button button--ghost" href="<?= e(route_url('home')) ?>"><?= e(t('common.back_to_store')) ?></a>
            <?php if ($adminAuthenticated): ?>
                <form action="<?= e(url('actions/admin_logout.php')) ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="button button--ghost" type="submit"><?= e(t('admin.logout')) ?></button>
                </form>
            <?php endif; ?>
        </div>
    </header>

    <?php require BASE_PATH . '/templates/flash.php'; ?>

    <?php if (!$adminConfigured): ?>
        <section class="admin-panel admin-setup-panel">
            <h2><?= e(t('admin.disabled')) ?></h2>
            <p><?= e(t('admin.disabled_text')) ?></p>
            <code>php -r "echo password_hash('strong-password', PASSWORD_DEFAULT), PHP_EOL;"</code>
        </section>
    <?php elseif (!$adminAuthenticated): ?>
        <?php require BASE_PATH . '/templates/admin/login.php'; ?>
    <?php else: ?>
        <?php require BASE_PATH . '/templates/admin/navigation.php'; ?>
        <main class="admin-content">
            <?php require BASE_PATH . '/templates/admin/' . $adminSection . '.php'; ?>
        </main>
    <?php endif; ?>
</div>
