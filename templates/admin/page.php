<?php
$adminLanguage = current_language();
$adminNextLanguage = alternate_language();
$adminLanguageReturnTo = current_public_return_path();
?>
<div class="admin-shell">
    <header class="admin-topbar">
        <div>
            <p class="admin-eyebrow">Atlantic Anarchy</p>
            <h1><?= e(t('admin.title')) ?></h1>
            <p><?= e(t('admin.subtitle')) ?></p>
        </div>
        <div class="admin-topbar-actions">
            <form action="<?= e(url('actions/language.php')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="language" value="<?= e($adminNextLanguage) ?>">
                <input type="hidden" name="return_to" value="<?= e($adminLanguageReturnTo) ?>">
                <button
                    class="button button--ghost language-button"
                    type="submit"
                    aria-label="<?= e(t('language.switch_to_' . $adminNextLanguage)) ?>"
                >
                    <img
                        class="language-flag"
                        src="<?= e(url($adminLanguage === 'pt' ? 'assets/flag-pt.png' : 'assets/flag-en.png')) ?>"
                        alt=""
                        aria-hidden="true"
                    >
                    <span class="language-code"><?= e(language_label($adminLanguage)) ?></span>
                </button>
            </form>
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
