<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_post();
verify_csrf();

try {
    if (tebex_is_configured() && !tebex_coupons_enabled()) {
        throw new InvalidArgumentException(t('tebex.coupons_disabled'));
    }

    $code = request_string('coupon_code');
    $summary = cart_summary();
    $coupon = validate_coupon($code, (int) $summary['subtotal_cents']);
    $_SESSION['coupon_code'] = (string) $coupon['code'];
    flash('success', t('messages.coupon_applied'));
} catch (InvalidArgumentException $error) {
    flash('error', $error->getMessage());
}

redirect_route('cart');
