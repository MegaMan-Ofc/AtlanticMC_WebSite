<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_get();
require_admin();
enforce_rate_limit('admin_analytics_ajax', 90, 60);

$days = admin_analytics_normalize_days(query_int('days', 30));
$adminSummary = admin_dashboard_summary();
$adminAnalytics = admin_dashboard_analytics($days);
$adminTraffic = daily_traffic_stats(7);

ob_start();
require TEMPLATE_PATH . '/admin/analytics/dashboard-content.php';
$html = ob_get_clean();

json_response([
    'ok' => true,
    'data' => [
        'html' => is_string($html) ? $html : '',
        'days' => $days,
        'label' => admin_analytics_period_label($days),
    ],
]);
