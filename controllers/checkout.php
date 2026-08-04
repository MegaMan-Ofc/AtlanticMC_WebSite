<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_minecraft_recipient('checkout.php');

$cart = cart_summary();

if ($cart['items'] === []) {
    flash('info', 'O carrinho está vazio.');
    redirect('cart.php');
}

$pageTitle = 'Atlantic Anarchy - Checkout';
$pageDescription = 'Confirm your Atlantic Anarchy order.';
$bodyClass = 'page-checkout';
$pageStyles = ['css/pages/checkout.css'];
$checkoutRecipient = current_minecraft_recipient();
