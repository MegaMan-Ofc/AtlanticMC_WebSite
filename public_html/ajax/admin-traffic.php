<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_get();
require_admin();
enforce_rate_limit('admin_traffic_ajax', 120, 60);

$days = query_int('days', 7);
$days = $days >= 30 ? 30 : 7;
$adminTraffic = daily_traffic_stats($days);
$adminTrafficExpanded = $days > 7;

ob_start();
require BASE_PATH . '/templates/admin/traffic-widget-content.php';
$html = ob_get_clean();

json_response([
    'ok' => true,
    'data' => [
        'html' => is_string($html) ? $html : '',
        'days' => $days,
    ],
]);
