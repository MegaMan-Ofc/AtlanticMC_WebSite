<?php require_once __DIR__ . '/controllers/login.php'; ?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require __DIR__ . '/includes/header.php'; ?>
    <main class="main-content login-main" id="main">
        <div class="container login-shell">
            <section class="login-card">
                <a class="login-back" href="<?= e(url('index.php')) ?>" aria-label="Back to store">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </a>

                <?php if ($loginRecipient !== null): ?>
                    <div class="login-avatar">
                        <img src="<?= e($loginRecipient['avatar_url']) ?>" alt="<?= e($loginRecipient['username']) ?> Minecraft avatar">
                        <i class="fa-solid <?= $loginRecipient['platform'] === 'bedrock' ? 'fa-mobile-screen' : 'fa-cube' ?>" aria-hidden="true"></i>
                    </div>
                    <h1>Logged in as <?= e($loginRecipient['username']) ?></h1>
                    <p>
                        This Minecraft account will receive the products purchased in the store.
                        It does not need to belong to the person making the payment.
                    </p>
                    <dl class="login-details">
                        <div>
                            <dt>Platform</dt>
                            <dd><?= e(ucfirst($loginRecipient['platform'])) ?></dd>
                        </div>
                        <div>
                            <dt>Recipient</dt>
                            <dd><?= e($loginRecipient['username']) ?></dd>
                        </div>
                    </dl>
                    <form action="<?= e(url('actions/logout.php')) ?>" method="post" class="login-form">
                        <?= csrf_field() ?>
                        <button class="button button--danger button--wide" type="submit">
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                            Logout
                        </button>
                    </form>
                <?php else: ?>
                    <div class="login-avatar">
                        <img src="<?= e(url('assets/steve.png')) ?>" alt="Default Minecraft avatar">
                        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                    </div>
                    <h1>Choose a Minecraft account</h1>
                    <p>
                        Enter the name of the account that should receive the purchase.
                        You can also use another player's name to send a gift.
                    </p>
                    <form action="<?= e(url('actions/login.php')) ?>" method="post" class="login-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">

                        <fieldset class="platform-selector">
                            <legend class="sr-only">Minecraft platform</legend>
                            <label class="platform-option">
                                <input type="radio" name="platform" value="java" checked>
                                <span class="platform-btn">
                                    <i class="fa-solid fa-cube" aria-hidden="true"></i>
                                    Java
                                </span>
                            </label>
                            <label class="platform-option">
                                <input type="radio" name="platform" value="bedrock">
                                <span class="platform-btn">
                                    <i class="fa-solid fa-mobile-screen" aria-hidden="true"></i>
                                    Bedrock
                                </span>
                            </label>
                        </fieldset>

                        <label class="login-field" for="username-input">
                            <span>Minecraft username</span>
                            <input
                                class="field"
                                id="username-input"
                                name="username"
                                type="text"
                                minlength="2"
                                maxlength="16"
                                autocomplete="off"
                                placeholder="Enter the Minecraft username"
                                pattern="[A-Za-z0-9_.]+"
                                required
                            >
                        </label>
                        <small class="login-help">
                            For Bedrock, the initial dot is added automatically when it is missing.
                        </small>

                        <button class="button button--primary button--wide" type="submit">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            Continue
                        </button>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <?php require __DIR__ . '/includes/footer.php'; ?>
</div>
</body>
</html>
