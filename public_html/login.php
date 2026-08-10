<?php require_once dirname(__DIR__) . '/controllers/login.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require dirname(__DIR__) . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div id="wrap">
    <?php require dirname(__DIR__) . '/includes/header.php'; ?>
    <main class="main-content login-main" id="main">
        <div class="container login-shell">
            <section class="login-card">
                <a
                    class="login-back"
                    href="<?= e(route_url('home')) ?>"
                    aria-label="<?= e(t('login.back_aria')) ?>"
                >
                    <i
                        class="fa-solid fa-arrow-left"
                        aria-hidden="true"
                    ></i>
                </a>

                <?php if ($loginRecipient !== null): ?>
                    <div class="login-avatar">
                        <img
                            src="<?= e($loginRecipient['avatar_url']) ?>"
                            alt="<?= e(
                                t(
                                    'login.avatar_alt',
                                    ['username' => $loginRecipient['username']]
                                )
                            ) ?>"
                        >
                        <i
                            class="fa-solid fa-cube"
                            aria-hidden="true"
                        ></i>
                    </div>

                    <h1>
                        <?= e(
                            t(
                                'login.logged_as',
                                ['username' => $loginRecipient['username']]
                            )
                        ) ?>
                    </h1>

                    <p><?= e(t('login.recipient_explanation')) ?></p>

                    <dl class="login-details">
                        <div>
                            <dt><?= e(t('common.platform')) ?></dt>
                            <dd>
                                <?= e(
                                    localized_platform(
                                        $loginRecipient['platform']
                                    )
                                ) ?>
                            </dd>
                        </div>
                        <div>
                            <dt><?= e(t('common.recipient')) ?></dt>
                            <dd><?= e($loginRecipient['username']) ?></dd>
                        </div>
                    </dl>

                    <form
                        action="<?= e(url('actions/logout.php')) ?>"
                        method="post"
                        class="login-form"
                    >
                        <?= csrf_field() ?>
                        <button
                            class="button button--danger button--wide"
                            type="submit"
                        >
                            <i
                                class="fa-solid fa-right-from-bracket"
                                aria-hidden="true"
                            ></i>
                            <?= e(t('common.logout')) ?>
                        </button>
                    </form>
                <?php else: ?>
                    <div class="login-avatar">
                        <img
                            src="<?= e(url('assets/steve.png')) ?>"
                            alt="<?= e(t('login.default_avatar_alt')) ?>"
                        >
                        <i
                            class="fa-solid fa-user-plus"
                            aria-hidden="true"
                        ></i>
                    </div>

                    <h1><?= e(t('login.choose_title')) ?></h1>
                    <p><?= e(t('login.choose_text')) ?></p>

                    <form
                        action="<?= e(url('actions/login.php')) ?>"
                        method="post"
                        class="login-form"
                    >
                        <?= csrf_field() ?>

                        <input
                            type="hidden"
                            name="return_to"
                            value="<?= e($returnTo) ?>"
                        >

                        <fieldset class="platform-selector">
                            <legend class="sr-only">
                                <?= e(t('login.platform_legend')) ?>
                            </legend>

                            <label class="platform-option">
                                <input
                                    type="radio"
                                    name="platform"
                                    value="java"
                                    checked
                                >
                                <span class="platform-btn">
                                    <i
                                        class="fa-solid fa-cube"
                                        aria-hidden="true"
                                    ></i>
                                    Java
                                </span>
                            </label>

                            <label
                                class="platform-option platform-option--disabled"
                                aria-disabled="true"
                                title="<?= e(t('login.bedrock_disabled_help')) ?>"
                            >
                                <input
                                    type="radio"
                                    name="platform"
                                    value="bedrock"
                                    disabled
                                >
                                <span class="platform-btn">
                                    <i
                                        class="fa-solid fa-mobile-screen"
                                        aria-hidden="true"
                                    ></i>
                                    <span>Bedrock</span>
                                    <small class="platform-status">
                                        <?= e(t('login.unavailable')) ?>
                                    </small>
                                </span>
                            </label>
                        </fieldset>

                        <small class="login-help login-help--notice">
                            <?= e(t('login.bedrock_disabled_help')) ?>
                        </small>

                        <label
                            class="login-field"
                            for="username-input"
                        >
                            <span><?= e(t('login.username_label')) ?></span>
                            <input
                                class="field"
                                id="username-input"
                                name="username"
                                type="text"
                                minlength="3"
                                maxlength="16"
                                autocomplete="off"
                                placeholder="<?= e(t('login.username_placeholder')) ?>"
                                pattern="[A-Za-z0-9_]+"
                                required
                            >
                        </label>

                        <small class="login-help">
                            <?= e(t('login.java_help')) ?>
                        </small>

                        <button
                            class="button button--primary button--wide"
                            type="submit"
                        >
                            <i
                                class="fa-solid fa-check"
                                aria-hidden="true"
                            ></i>
                            <?= e(t('common.continue')) ?>
                        </button>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <?php require dirname(__DIR__) . '/includes/footer.php'; ?>
</div>
</body>
</html>
