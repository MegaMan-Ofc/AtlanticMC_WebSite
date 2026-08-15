<?php

declare(strict_types=1);

$dashboardSource = file_get_contents($root . '/includes/admin_dashboard.php');
$analyticsSource = file_get_contents($root . '/includes/analytics.php');
$adminAnalyticsSource = file_get_contents($root . '/includes/admin_analytics.php');
$overviewTemplate = file_get_contents($root . '/templates/admin/overview.php');
$analyticsTemplate = file_get_contents($root . '/templates/admin/analytics-dashboard-content.php');
$trafficTemplate = file_get_contents($root . '/templates/admin/traffic-widget-content.php');
$adminJavaScript = file_get_contents($root . '/public_html/js/admin.js');
$adminStyles = file_get_contents($root . '/public_html/css/pages/admin.css');
$mysqlAnalyticsMigration = file_get_contents($root . '/database/migrations/mysql/009_admin_analytics.php');

$assert(
    is_string($dashboardSource)
        && !str_contains($dashboardSource, "'analytics'")
        && str_contains($dashboardSource, "const ADMIN_SECTIONS = ['overview', 'categories', 'products', 'recommended', 'coupons', 'orders', 'maintenance'];"),
    'Daily access analytics are consolidated into the overview instead of a separate admin section.'
);

$assert(
    is_file($root . '/database/migrations/sqlite/009_admin_analytics.php')
        && is_file($root . '/database/migrations/mysql/009_admin_analytics.php')
        && is_file($root . '/database/migrations/sqlite/010_analytics_integrity.php')
        && is_file($root . '/database/migrations/mysql/010_analytics_integrity.php')
        && is_file($root . '/public_html/ajax/admin-traffic.php')
        && is_file($root . '/public_html/ajax/admin-analytics.php'),
    'Analytics migrations and authenticated overview AJAX endpoints exist.'
);

$assert(
    is_string($mysqlAnalyticsMigration)
        && str_contains($mysqlAnalyticsMigration, 'product_id BIGINT UNSIGNED NOT NULL'),
    'The MySQL product analytics foreign key matches the BIGINT products primary key.'
);

$assert(
    is_string($analyticsSource)
        && str_contains($analyticsSource, 'daily_route_stats')
        && str_contains($analyticsSource, 'daily_product_stats')
        && str_contains($analyticsSource, 'track_product_impressions')
        && str_contains($analyticsSource, 'track_product_interaction')
        && str_contains($analyticsSource, 'track_product_cart_add')
        && str_contains($analyticsSource, 'store_category_by_slug($slug, false)'),
    'Public analytics collect route traffic and product funnel events while rejecting invalid category traffic.'
);

$assert(
    is_string($adminAnalyticsSource)
        && str_contains($adminAnalyticsSource, 'admin_dashboard_analytics')
        && str_contains($adminAnalyticsSource, 'admin_analytics_product_rows')
        && str_contains($adminAnalyticsSource, 'admin_analytics_category_rows')
        && str_contains($adminAnalyticsSource, 'admin_analytics_top_pages')
        && str_contains($adminAnalyticsSource, 'ADMIN_ANALYTICS_PERIODS')
        && !str_contains($adminAnalyticsSource, 'COALESCE(paid_at, updated_at)'),
    'Administrator analytics use a dedicated query layer, selectable periods and the stable paid timestamp.'
);

$assert(
    is_string($overviewTemplate)
        && str_contains($overviewTemplate, 'data-admin-analytics-dashboard')
        && is_string($analyticsTemplate)
        && str_contains($analyticsTemplate, 'data-admin-analytics-period')
        && str_contains($analyticsTemplate, 'data-admin-traffic-widget')
        && str_contains($analyticsTemplate, 'admin-funnel')
        && str_contains($analyticsTemplate, 'admin-category-performance')
        && str_contains($analyticsTemplate, 'admin-analytics-grid--four'),
    'The overview combines period selection, traffic, funnel, product and category analytics.'
);

$assert(
    is_string($adminJavaScript)
        && str_contains($adminJavaScript, 'data-admin-traffic-toggle')
        && str_contains($adminJavaScript, 'loadAdminTraffic')
        && str_contains($adminJavaScript, 'loadAdminAnalytics')
        && str_contains($adminJavaScript, 'data-admin-analytics-period')
        && is_string($adminStyles)
        && str_contains($adminStyles, '.admin-traffic-chart')
        && str_contains($adminStyles, '.admin-analytics-grid--four')
        && str_contains($adminStyles, '.admin-analytics-toolbar'),
    'Traffic history and analytics periods update with AJAX and responsive styling.'
);

$assert(
    admin_analytics_max_metric([], 'value') === 1
        && admin_analytics_max_metric([['value' => 0], ['value' => 12]], 'value') === 12
        && admin_analytics_max_metric([['other' => 5]], 'value', 3) === 3,
    'Analytics chart maxima are safe when rankings or traffic datasets are empty.'
);

$assert(
    admin_analytics_normalize_days(7) === 7
        && admin_analytics_normalize_days(30) === 30
        && admin_analytics_normalize_days(90) === 90
        && admin_analytics_normalize_days(36500) === 36500
        && admin_analytics_normalize_days(12) === 30,
    'Analytics periods are restricted to the supported dashboard choices.'
);

$assert(
    is_string($analyticsTemplate)
        && is_string($trafficTemplate)
        && !str_contains($analyticsTemplate, 'max(1, ...array_map')
        && !str_contains($trafficTemplate, 'max(1, ...array_map')
        && str_contains($analyticsTemplate, 'admin_analytics_max_metric')
        && str_contains($trafficTemplate, 'admin_analytics_max_metric'),
    'Admin analytics templates use the empty-safe maximum helper.'
);
