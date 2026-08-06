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
            <div class="admin-password-control">
                <input id="admin-password" name="password" type="password" autocomplete="current-password" required>
                <button class="admin-password-toggle" type="button" data-password-toggle data-show-label="<?= e(t('admin.show_password')) ?>" data-hide-label="<?= e(t('admin.hide_password')) ?>" aria-controls="admin-password" aria-pressed="false" aria-label="<?= e(t('admin.show_password')) ?>" title="<?= e(t('admin.show_password')) ?>">
                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <button class="button button--primary admin-login-submit" type="submit"><?= e(t('common.login')) ?></button>
    </form>
</section>
