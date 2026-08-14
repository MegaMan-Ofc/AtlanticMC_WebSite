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
$adminCategories = [];
$adminCategoryOptions = [];
$adminProductFilters = [];
$adminProducts = [];
$adminRecommendedSlots = [];
$adminRecommendedOptions = [];
$adminHomeCategoryLayout = ['top' => null, 'grid' => [], 'bottom' => null];
$adminCouponFilters = [];
$adminCoupons = [];
$adminOrderFilters = [];
$adminOrdersPage = ['orders' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
$adminTraffic = [];

if ($adminAuthenticated) {
    $adminSummary = admin_dashboard_summary();

    if ($adminSection === 'categories') {
        $adminCategories = all_categories_admin();
    } elseif ($adminSection === 'products') {
        $adminCategoryOptions = all_store_categories(true);
        $adminProductFilters = admin_product_filters();
        $adminProducts = all_products_admin($adminProductFilters);
    } elseif ($adminSection === 'recommended') {
        $adminRecommendedSlots = admin_recommended_slots();
        $adminRecommendedOptions = admin_recommended_product_options();
        $adminHomeCategoryLayout = admin_home_category_layout();
    } elseif ($adminSection === 'coupons') {
        $adminCouponFilters = admin_coupon_filters();
        $adminCoupons = all_coupons_admin($adminCouponFilters);
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
        'categories' => 'fa-solid fa-layer-group',
        'products' => 'fa-solid fa-tags',
        'recommended' => 'fa-solid fa-house',
        'coupons' => 'fa-solid fa-ticket',
        'orders' => 'fa-solid fa-receipt',
        'analytics' => 'fa-solid fa-chart-column',
        default => 'fa-solid fa-gauge-high',
    };
}
