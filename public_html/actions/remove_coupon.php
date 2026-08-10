<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_post();
verify_csrf();
unset($_SESSION['coupon_code']);
flash('success', t('messages.coupon_removed'));
redirect_route('cart');
