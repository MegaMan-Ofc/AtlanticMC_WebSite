<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();

$returnTo = safe_return_path(request_string('return_to'), 'index.php');

try {
    cart_add(request_int('product_id'), max(1, request_int('quantity', 1)));
    flash('success', t('messages.cart_added'));
} catch (InvalidArgumentException $error) {
    flash('error', $error->getMessage());
}

redirect($returnTo);
