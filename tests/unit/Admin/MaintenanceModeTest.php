<?php

declare(strict_types=1);

@unlink($maintenanceStatePath);

$initialState = maintenance_state();
$assert(
    maintenance_state_path() === $maintenanceStatePath
        && maintenance_is_enabled($initialState) === false
        && ($initialState['integrity_ok'] ?? false) === true,
    'Maintenance mode defaults to a healthy disabled state in the configured private storage path.'
);

$assert(
    maintenance_confirmation_phrase(true) === 'ATIVAR MANUTENCAO'
        && maintenance_confirmation_phrase(false) === 'ABRIR LOJA',
    'Maintenance transitions use distinct explicit confirmation phrases.'
);

$future = date('Y-m-d\TH:i', time() + 7200);
$enabledState = maintenance_set_state(true, '  Atualização   importante em curso.  ', $future, 'test-admin');
$storedState = maintenance_state();
$storedRaw = file_get_contents($maintenanceStatePath);

$assert(
    is_file($maintenanceStatePath)
        && maintenance_is_enabled($enabledState)
        && maintenance_is_enabled($storedState)
        && $storedState['message'] === 'Atualização importante em curso.'
        && $storedState['updated_by'] === 'test-admin'
        && is_string($storedState['ends_at'])
        && ($storedState['integrity_ok'] ?? false) === true,
    'Maintenance state is normalized and persisted atomically outside tracked source files.'
);

$assert(
    is_string($storedRaw)
        && !str_contains($storedRaw, 'test-password')
        && !str_contains($storedRaw, (string) config('admin.password_hash')),
    'Maintenance state never persists administrator credentials.'
);

$retryAfter = maintenance_retry_after_seconds($storedState);
$assert(
    $retryAfter >= 60 && $retryAfter <= 86400,
    'Maintenance responses expose a bounded retry interval.'
);

$disabledState = maintenance_set_state(false, 'ignored', $future, 'test-admin');
$assert(
    maintenance_is_enabled($disabledState) === false
        && $disabledState['message'] === ''
        && $disabledState['ends_at'] === null,
    'Reopening the store clears public maintenance-only metadata.'
);

file_put_contents($maintenanceStatePath, '{invalid-json');
$failedState = maintenance_state();
$assert(
    maintenance_is_enabled($failedState)
        && ($failedState['integrity_ok'] ?? true) === false,
    'Unreadable maintenance state fails closed instead of silently reopening the store.'
);

$repairedState = maintenance_set_state(false, '', '', 'test-admin');
$assert(
    maintenance_is_enabled($repairedState) === false
        && ($repairedState['integrity_ok'] ?? false) === true,
    'A confirmed administrator transition repairs an invalid maintenance state file.'
);

$originalScriptName = $_SERVER['SCRIPT_NAME'] ?? null;
$originalScriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? null;

$_SERVER['SCRIPT_NAME'] = '/admin.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/public_html/admin.php';
$adminAllowed = maintenance_request_is_allowed();

$_SERVER['SCRIPT_NAME'] = '/actions/admin_set_maintenance.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/public_html/actions/admin_set_maintenance.php';
$adminActionAllowed = maintenance_request_is_allowed();

$_SERVER['SCRIPT_NAME'] = '/api/tebex_webhook.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/public_html/api/tebex_webhook.php';
$webhookAllowed = maintenance_request_is_allowed();

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/public_html/index.php';
$storefrontAllowed = maintenance_request_is_allowed();

if ($originalScriptName === null) {
    unset($_SERVER['SCRIPT_NAME']);
} else {
    $_SERVER['SCRIPT_NAME'] = $originalScriptName;
}

if ($originalScriptFilename === null) {
    unset($_SERVER['SCRIPT_FILENAME']);
} else {
    $_SERVER['SCRIPT_FILENAME'] = $originalScriptFilename;
}

$assert(
    $adminAllowed && $adminActionAllowed && $webhookAllowed && !$storefrontAllowed,
    'Maintenance keeps recovery and Tebex administration paths available while blocking storefront requests.'
);

$bootstrapSource = file_get_contents($root . '/app/bootstrap.php');
$actionSource = file_get_contents($root . '/public_html/actions/admin_set_maintenance.php');
$adminTemplateSource = file_get_contents($root . '/templates/admin/maintenance/index.php');
$maintenanceControllerSource = file_get_contents($root . '/controllers/Site/maintenance.php');
$maintenancePageSource = file_get_contents($root . '/public_html/maintenance.php');
$gitignoreSource = file_get_contents($root . '/.gitignore');
$adminDashboardSource = file_get_contents($root . '/app/Admin/Dashboard/dashboard.php');
$adminCssSource = file_get_contents($root . '/public_html/css/pages/admin.css');

$assert(
    is_string($bootstrapSource)
        && strpos($bootstrapSource, 'enforce_maintenance_mode();') < strpos($bootstrapSource, 'track_public_page_view();'),
    'Maintenance enforcement runs before public analytics or storefront controller work.'
);

$assert(
    is_string($actionSource)
        && str_contains($actionSource, 'verify_csrf();')
        && str_contains($actionSource, 'require_admin();')
        && str_contains($actionSource, "enforce_rate_limit('admin_maintenance_change'")
        && str_contains($actionSource, 'admin_password_is_valid')
        && str_contains($actionSource, 'maintenance_confirmation_phrase')
        && str_contains($actionSource, 'maintenance_state_changed'),
    'Maintenance state changes require CSRF, authentication, rate limiting, reauthentication, explicit phrase and stale-state protection.'
);

$assert(
    is_string($adminTemplateSource)
        && substr_count($adminTemplateSource, 'data-admin-maintenance-step=') >= 3
        && str_contains($adminTemplateSource, 'name="acknowledge_impact"')
        && str_contains($adminTemplateSource, 'name="confirmation_phrase"')
        && str_contains($adminTemplateSource, 'name="password"'),
    'The administrator maintenance dialog presents three deliberate verification stages.'
);

$assert(
    is_string($maintenanceControllerSource)
        && str_contains($maintenanceControllerSource, 'http_response_code(503)')
        && str_contains($maintenanceControllerSource, 'Retry-After: ')
        && str_contains($maintenanceControllerSource, 'X-Robots-Tag: noindex, nofollow')
        && is_string($maintenancePageSource)
        && str_contains($maintenancePageSource, 'maintenance.http_status'),
    'The public maintenance experience returns a real non-indexable HTTP 503 response.'
);

$assert(
    is_string($gitignoreSource)
        && str_contains($gitignoreSource, 'storage/maintenance.json')
        && str_contains($gitignoreSource, 'storage/maintenance-*.tmp')
        && is_string($adminDashboardSource)
        && str_contains($adminDashboardSource, "'maintenance'"),
    'Runtime maintenance state stays out of the public repository and maintenance is isolated as an administrator section.'
);

$assert(
    is_string($adminCssSource)
        && str_contains($adminCssSource, '.admin-maintenance-dialog [hidden]')
        && preg_match('/\.admin-maintenance-dialog \[hidden\]\s*\{[^}]*display:\s*none;/s', $adminCssSource) === 1,
    'Maintenance dialog hidden steps and actions remain visually hidden despite component display rules.'
);

@unlink($maintenanceStatePath);
