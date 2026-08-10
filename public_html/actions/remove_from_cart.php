<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_post();
verify_csrf();
cart_remove(request_int('product_id'));
flash('success', t('messages.cart_removed'));
redirect_route('cart');
