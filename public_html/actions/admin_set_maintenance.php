<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_post();
verify_csrf();
require_admin();
enforce_rate_limit('admin_maintenance_change', 12, 900);

try {
    $state = maintenance_state();
    $currentEnabled = maintenance_is_enabled($state);
    $currentValue = $currentEnabled ? 'enabled' : 'disabled';
    $targetValue = request_string('target_state');
    $postedCurrentValue = request_string('current_state');

    if (!hash_equals($currentValue, $postedCurrentValue)) {
        throw new InvalidArgumentException(t('validation.maintenance_state_changed'));
    }

    if (!in_array($targetValue, ['enabled', 'disabled'], true)) {
        throw new InvalidArgumentException(t('validation.maintenance_invalid_transition'));
    }

    $enable = $targetValue === 'enabled';

    if ($enable === $currentEnabled) {
        throw new InvalidArgumentException(t('validation.maintenance_invalid_transition'));
    }

    if (request_int('acknowledge_impact') !== 1 || request_int('acknowledge_access') !== 1) {
        throw new InvalidArgumentException(t('validation.maintenance_acknowledgement'));
    }

    $expectedPhrase = maintenance_confirmation_phrase($enable);

    if (!hash_equals($expectedPhrase, request_string('confirmation_phrase'))) {
        throw new InvalidArgumentException(t('validation.maintenance_confirmation'));
    }

    if (!admin_password_is_valid(request_string('password'))) {
        $username = (string) ($_SESSION['admin']['username'] ?? '');
        security_log('warning', 'admin_maintenance_reauthentication_failed', [
            'username_hash' => hash('sha256', strtolower($username)),
        ]);
        throw new InvalidArgumentException(t('validation.maintenance_password'));
    }

    $username = (string) ($_SESSION['admin']['username'] ?? config('admin.username', ''));
    maintenance_set_state(
        $enable,
        request_string('message'),
        request_string('ends_at'),
        $username
    );

    security_log('info', $enable ? 'maintenance_mode_enabled' : 'maintenance_mode_disabled', [
        'username_hash' => hash('sha256', strtolower($username)),
    ]);
    flash('success', t($enable ? 'messages.maintenance_enabled' : 'messages.maintenance_disabled'));
} catch (InvalidArgumentException $error) {
    flash('error', $error->getMessage());
} catch (Throwable $error) {
    log_exception($error);
    flash('error', t('messages.maintenance_change_failed'));
}

redirect_admin('maintenance');
