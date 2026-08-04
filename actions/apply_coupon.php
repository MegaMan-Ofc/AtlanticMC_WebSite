<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();

try {
    $code = request_string('coupon_code');
    $summary = cart_summary();
    $coupon = validate_coupon($code, (int) $summary['subtotal_cents']);
    $_SESSION['coupon_code'] = (string) $coupon['code'];
    flash('success', 'Cupão aplicado com sucesso.');
} catch (InvalidArgumentException $error) {
    flash('error', $error->getMessage());
}

redirect('cart.php');
