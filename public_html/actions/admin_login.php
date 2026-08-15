<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_post();
verify_csrf();
$result = admin_attempt_login(request_string('username'), request_string('password'));

if ($result === ADMIN_LOGIN_SUCCESS) {
    flash('success', t('messages.admin_session_started'));
} elseif ($result === ADMIN_LOGIN_LOCKED) {
    flash('error', t('messages.admin_login_locked'));
} elseif ($result === ADMIN_LOGIN_DISABLED) {
    flash('error', t('messages.admin_not_configured'));
} else {
    flash('error', t('messages.admin_invalid_credentials'));
}

redirect_route('admin');
