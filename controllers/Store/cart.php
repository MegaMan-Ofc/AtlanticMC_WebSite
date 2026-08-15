<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$pageTitle = t('cart.page_title');
$pageDescription = t('cart.page_description');
$bodyClass = 'page-cart';
$pageStyles = ['css/pages/cart.css'];
$cart = cart_summary();
