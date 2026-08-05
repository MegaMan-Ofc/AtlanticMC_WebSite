<?php
$headerRecipient = current_minecraft_recipient();
$headerCartCount = cart_count();
?>
<header class="site-header" id="top">
    <div class="header-primary">
        <div class="container header-grid">
            <a aria-label="Join our Discord server" class="header-link header-link--discord" href="<?= e(config('app.discord_url')) ?>" rel="noopener noreferrer" target="_blank">
                <i aria-hidden="true" class="fa-brands fa-discord"></i>
                <span><small>Join our</small><strong>Discord</strong></span>
            </a>
            <a aria-label="Atlantic Anarchy store home" class="brand" href="<?= e(url('index.php')) ?>">
                <img alt="Atlantic Anarchy" src="<?= e(url('assets/logo1.png')) ?>">
            </a>
            <button aria-label="Copy the Minecraft server address" class="header-link header-link--server" data-copy-value="<?= e(config('app.server_ip')) ?>" title="Click to copy" type="button">
                <i aria-hidden="true" class="fa-solid fa-server"></i>
                <span><small>Server IP</small><strong><?= e(config('app.server_ip')) ?></strong></span>
            </button>
        </div>
    </div>
    <div class="header-secondary">
        <div class="container header-row">
            <a aria-label="Choose Minecraft purchase recipient" class="user-card" href="<?= e(url('login.php')) ?>">
                <?php if ($headerRecipient === null): ?>
                    <img alt="Minecraft avatar" src="<?= e(url('assets/steve.png')) ?>">
                    <span><small>Logged in as</small><strong>Guest</strong></span>
                <?php else: ?>
                    <img alt="<?= e($headerRecipient['username']) ?> Minecraft avatar" src="<?= e($headerRecipient['avatar_url']) ?>">
                    <span><small>Logged in as</small><strong><?= e($headerRecipient['username']) ?></strong></span>
                <?php endif; ?>
            </a>
            <nav aria-label="Store actions" class="toolbar">
                <button class="button button--ghost language-button" type="button" aria-label="Switch language to English">
                    <img class="language-flag" src="<?= e(url('assets/flag-pt.png')) ?>" data-pt-src="<?= e(url('assets/flag-pt.png')) ?>" data-en-src="<?= e(url('assets/flag-en.png')) ?>" alt="" aria-hidden="true">
                    <span class="language-code">PT</span>
                </button>
                <a aria-label="Open shopping cart" class="button button--primary cart-button" href="<?= e(url('cart.php')) ?>">
                    <i aria-hidden="true" class="fa-solid fa-cart-shopping"></i>
                    <span class="cart-count" data-cart-count<?= $headerCartCount === 0 ? ' hidden' : '' ?>><?= $headerCartCount ?></span>
                </a>
            </nav>
        </div>
    </div>
</header>
<?php require BASE_PATH . '/templates/flash.php'; ?>
