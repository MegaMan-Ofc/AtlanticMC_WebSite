<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Atlantic Anarchy - Cart';
$pageDescription = 'Review the products stored securely in your cart session.';
$bodyClass = 'page-cart';
$pageStyles = ['css/pages/cart.css'];
$cart = cart_summary();
