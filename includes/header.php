<?php
$headerRecipient = current_minecraft_recipient();
$headerCartCount = cart_count();
$headerLanguage = current_language();
$nextLanguage = alternate_language();
$languageReturnTo = basename(current_request_path());
?>
<header class="site-header" id="top">
    <div class="header-primary">
        <div class="container header-grid">
            <a aria-label="<?= e(t('header.join_discord_aria')) ?>" class="header-link header-link--discord" href="<?= e(config('app.discord_url')) ?>" rel="noopener noreferrer" target="_blank">
                <i aria-hidden="true" class="fa-brands fa-discord"></i>
                <span><small><?= e(t('header.join_our')) ?></small><strong>Discord</strong></span>
            </a>
            <a aria-label="<?= e(t('header.store_home_aria')) ?>" class="brand" href="<?= e(url('index.php')) ?>">
                <img alt="Atlantic Anarchy" src="<?= e(url('assets/logo1.png')) ?>">
            </a>
            <button aria-label="<?= e(t('header.copy_server_aria')) ?>" class="header-link header-link--server" data-copy-value="<?= e(config('app.server_ip')) ?>" title="<?= e(t('header.click_to_copy')) ?>" type="button">
                <i aria-hidden="true" class="fa-solid fa-server"></i>
                <span><small><?= e(t('header.server_ip')) ?></small><strong><?= e(config('app.server_ip')) ?></strong></span>
            </button>
        </div>
    </div>
    <div class="header-secondary">
        <div class="container header-row">
            <a aria-label="<?= e(t('header.choose_recipient_aria')) ?>" class="user-card" href="<?= e(url('login.php')) ?>">
                <?php if ($headerRecipient === null): ?>
                    <img alt="<?= e(t('header.minecraft_avatar')) ?>" src="<?= e(url('assets/steve.png')) ?>">
                    <span><small><?= e(t('header.logged_in_as')) ?></small><strong><?= e(t('header.guest')) ?></strong></span>
                <?php else: ?>
                    <img alt="<?= e(t('header.user_avatar', ['username' => $headerRecipient['username']])) ?>" src="<?= e($headerRecipient['avatar_url']) ?>">
                    <span><small><?= e(t('header.logged_in_as')) ?></small><strong><?= e($headerRecipient['username']) ?></strong></span>
                <?php endif; ?>
            </a>
            <nav aria-label="<?= e(t('header.store_actions')) ?>" class="toolbar">
                <form
                    action="<?= e(url('actions/language.php')) ?>"
                    method="post"
                    data-ajax-language
                    data-ajax-url="<?= e(url('ajax/language.php')) ?>"
                >
                    <?= csrf_field() ?>
                    <input type="hidden" name="language" value="<?= e($nextLanguage) ?>">
                    <input type="hidden" name="return_to" value="<?= e($languageReturnTo) ?>">
                    <button
                        class="button button--ghost language-button"
                        type="submit"
                        aria-label="<?= e(t('language.switch_to_' . $nextLanguage)) ?>"
                    >
                        <img
                            class="language-flag"
                            src="<?= e(url($headerLanguage === 'pt' ? 'assets/flag-pt.png' : 'assets/flag-en.png')) ?>"
                            alt=""
                            aria-hidden="true"
                        >
                        <span class="language-code"><?= e(language_label($headerLanguage)) ?></span>
                    </button>
                </form>
                <a aria-label="<?= e(t('header.open_cart')) ?>" class="button button--primary cart-button" href="<?= e(url('cart.php')) ?>">
                    <i aria-hidden="true" class="fa-solid fa-cart-shopping"></i>
                    <span class="cart-count" data-cart-count<?= $headerCartCount === 0 ? ' hidden' : '' ?>><?= $headerCartCount ?></span>
                </a>
            </nav>
        </div>
    </div>
</header>
<?php require BASE_PATH . '/templates/flash.php'; ?>
