<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$maintenanceState = maintenance_state();

if (!maintenance_is_enabled($maintenanceState) && !defined('ATLANTIC_MAINTENANCE_RENDERING')) {
    redirect_route('home');
}

http_response_code(503);
header('Retry-After: ' . maintenance_retry_after_seconds($maintenanceState));
header('Cache-Control: no-store, max-age=0');
header('Pragma: no-cache');
header('X-Robots-Tag: noindex, nofollow');

$pageTitle = t('maintenance.page_title');
$pageDescription = t('maintenance.description');
$pageRobots = 'noindex, nofollow';
$bodyClass = 'page-maintenance';
$pageStyles = ['css/pages/maintenance.css'];
$maintenanceEndsTimestamp = is_string($maintenanceState['ends_at'] ?? null)
    ? strtotime((string) $maintenanceState['ends_at'])
    : false;
$maintenanceLanguage = current_language();
$maintenanceNextLanguage = alternate_language();
