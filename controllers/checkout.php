<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_authentication('checkout.php');

$cart = cart_summary();

if ($cart['items'] === []) {
    flash('info', 'O carrinho está vazio.');
    redirect('cart.php');
}

$pageTitle = 'Atlantic Anarchy - Checkout';
$pageDescription = 'Confirm your Atlantic Anarchy order.';
$bodyClass = 'page-checkout';
$pageStyles = ['css/pages/checkout.css'];
$checkoutUser = current_user();
