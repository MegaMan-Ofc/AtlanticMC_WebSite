<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
unset($_SESSION['coupon_code']);
flash('success', t('messages.coupon_removed'));
redirect('cart.php');
