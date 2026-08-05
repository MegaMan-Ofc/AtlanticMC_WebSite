<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = t('admin.page_title');
$pageDescription = t('admin.page_description');
$bodyClass = 'admin-page';
$pageStyles = ['css/pages/admin.css'];
$adminConfigured = config('admin.password_hash', '') !== '';
$adminAuthenticated = admin_is_authenticated();
$adminProducts = $adminAuthenticated ? all_products_admin() : [];
$adminCoupons = $adminAuthenticated ? all_coupons_admin() : [];
$adminOrders = $adminAuthenticated ? recent_orders_admin() : [];
