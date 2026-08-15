<?php

declare(strict_types=1);

$dashboardSource = file_get_contents($root . '/includes/admin_dashboard.php');
$analyticsSource = file_get_contents($root . '/includes/analytics.php');
$adminAnalyticsSource = file_get_contents($root . '/includes/admin_analytics.php');
$overviewTemplate = file_get_contents($root . '/templates/admin/overview.php');
$adminJavaScript = file_get_contents($root . '/public_html/js/admin.js');
$adminStyles = file_get_contents($root . '/public_html/css/pages/admin.css');

$assert(
    is_string($dashboardSource)
        && !str_contains($dashboardSource, "'analytics'")
        && str_contains($dashboardSource, "const ADMIN_SECTIONS = ['overview', 'categories', 'products', 'recommended', 'coupons', 'orders'];"),
    'Daily access analytics are consolidated into the overview instead of a separate admin section.'
);

$assert(
    is_file($root . '/database/migrations/sqlite/009_admin_analytics.php')
        && is_file($root . '/database/migrations/mysql/009_admin_analytics.php')
        && is_file($root . '/public_html/ajax/admin-traffic.php'),
    'Analytics database migrations and the authenticated traffic AJAX endpoint exist.'
);

$assert(
    is_string($analyticsSource)
        && str_contains($analyticsSource, 'daily_route_stats')
        && str_contains($analyticsSource, 'daily_product_stats')
        && str_contains($analyticsSource, 'track_product_impressions')
        && str_contains($analyticsSource, 'track_product_cart_add'),
    'Public analytics collect route traffic, product impressions and cart intent.'
);

$assert(
    is_string($adminAnalyticsSource)
        && str_contains($adminAnalyticsSource, 'admin_dashboard_analytics')
        && str_contains($adminAnalyticsSource, 'admin_analytics_product_rows')
        && str_contains($adminAnalyticsSource, 'admin_analytics_category_rows')
        && str_contains($adminAnalyticsSource, 'admin_analytics_top_pages'),
    'Administrator analytics are isolated in a dedicated query layer.'
);

$assert(
    is_string($overviewTemplate)
        && str_contains($overviewTemplate, 'data-admin-traffic-widget')
        && str_contains($overviewTemplate, 'admin-funnel')
        && str_contains($overviewTemplate, 'admin-category-performance')
        && str_contains($overviewTemplate, 'admin-ranked-list'),
    'The overview combines responsive traffic, sales funnel, product and category analytics.'
);

$assert(
    is_string($adminJavaScript)
        && str_contains($adminJavaScript, 'data-admin-traffic-toggle')
        && str_contains($adminJavaScript, 'loadAdminTraffic')
        && is_string($adminStyles)
        && str_contains($adminStyles, '.admin-traffic-chart')
        && str_contains($adminStyles, '.admin-analytics-grid--three'),
    'Traffic history expands with AJAX and the analytics dashboard has responsive styling.'
);
