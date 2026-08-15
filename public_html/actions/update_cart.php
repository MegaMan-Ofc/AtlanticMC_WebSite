<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_post();
verify_csrf();

$quantities = $_POST['quantities'] ?? [];
cart_update(is_array($quantities) ? $quantities : []);
flash('success', t('messages.cart_updated'));
redirect_route('cart');
