<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

require_minecraft_recipient(route_path('checkout'));

$cart = cart_summary();

if ($cart['items'] === []) {
    flash('info', t('messages.cart_empty'));
    redirect_route('cart');
}

$pageTitle = t('checkout.page_title');
$pageDescription = t('checkout.page_description');
$bodyClass = 'page-checkout';
$pageStyles = ['css/pages/checkout.css'];
$checkoutRecipient = current_minecraft_recipient();
