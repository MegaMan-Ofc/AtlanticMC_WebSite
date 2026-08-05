<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
cart_remove(request_int('product_id'));
flash('success', t('messages.cart_removed'));
redirect('cart.php');
