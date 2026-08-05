<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
require_admin();

try {
    delete_coupon_from_admin(request_int('id'));
    flash('success', t('messages.admin_coupon_deleted'));
} catch (Throwable $error) {
    flash('error', public_error_message($error, t('messages.admin_delete_failed')));
}

redirect_admin('coupons');
