<section class="admin-login-layout">
    <div class="admin-login-copy">
        <span class="admin-login-icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span>
        <h2><?= e(t('admin.login_title')) ?></h2>
        <p><?= e(t('admin.login_text')) ?></p>
        <ul>
            <li><?= e(t('admin.login_security_server')) ?></li>
            <li><?= e(t('admin.login_security_session')) ?></li>
            <li><?= e(t('admin.login_security_limit')) ?></li>
        </ul>
    </div>
    <form class="admin-panel admin-login-form" action="<?= e(url('actions/admin_login.php')) ?>" method="post">
        <?= csrf_field() ?>
        <div class="admin-field">
            <label for="admin-username"><?= e(t('common.username')) ?></label>
            <input id="admin-username" name="username" autocomplete="username" maxlength="120" required autofocus>
        </div>
        <div class="admin-field">
            <label for="admin-password"><?= e(t('common.password')) ?></label>
            <input id="admin-password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button class="button button--primary admin-login-submit" type="submit"><?= e(t('common.login')) ?></button>
    </form>
</section>
