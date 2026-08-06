<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = t('admin.page_title');
$pageDescription = t('admin.page_description');
$bodyClass = 'admin-page';
$pageStyles = ['css/pages/admin.css'];
$adminConfigured = admin_is_configured();
$adminAuthenticated = admin_is_authenticated();
$adminSection = admin_section();
$adminSummary = [];
$adminProductFilters = [];
$adminProducts = [];
$adminCoupons = [];
$adminOrderFilters = [];
$adminOrdersPage = ['orders' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
$adminTraffic = [];

if ($adminAuthenticated) {
    $adminSummary = admin_dashboard_summary();

    if ($adminSection === 'products') {
        $adminProductFilters = admin_product_filters();
        $adminProducts = all_products_admin($adminProductFilters);
    } elseif ($adminSection === 'coupons') {
        $adminCoupons = all_coupons_admin();
    } elseif ($adminSection === 'orders') {
        $adminOrderFilters = admin_order_filters();
        $adminOrdersPage = admin_orders_page($adminOrderFilters);
    } elseif ($adminSection === 'analytics') {
        $adminTraffic = daily_traffic_stats(30);
    }
}

function admin_section_icon(string $section): string
{
    return match ($section) {
        'products' => 'fa-solid fa-tags',
        'coupons' => 'fa-solid fa-ticket',
        'orders' => 'fa-solid fa-receipt',
        'analytics' => 'fa-solid fa-chart-column',
        default => 'fa-solid fa-gauge-high',
    };
}

function format_admin_coupon_discount(array $coupon): string
{
    if (($coupon['discount_type'] ?? '') === 'fixed') {
        return format_money((int) ($coupon['discount_value'] ?? 0));
    }

    return (int) ($coupon['discount_value'] ?? 0) . '%';
}

function format_admin_datetime(string $value): string
{
    $timestamp = strtotime($value);

    return $timestamp === false ? $value : date('d/m/Y H:i', $timestamp);
}

function format_admin_date(string $value): string
{
    $timestamp = strtotime($value);

    return $timestamp === false ? $value : date('d/m/Y', $timestamp);
}
