<?php

declare(strict_types=1);

const ADMIN_LOGIN_SUCCESS = 'success';
const ADMIN_LOGIN_INVALID = 'invalid';
const ADMIN_LOGIN_LOCKED = 'locked';
const ADMIN_LOGIN_DISABLED = 'disabled';

function admin_is_configured(): bool
{
    return trim((string) config('admin.username', '')) !== ''
        && trim((string) config('admin.password_hash', '')) !== '';
}

function admin_session_fingerprint(): string
{
    return hash('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'));
}

function admin_is_authenticated(): bool
{
    $session = $_SESSION['admin'] ?? null;

    if (!is_array($session) || ($session['authenticated'] ?? false) !== true) {
        return false;
    }

    $lastActivity = (int) ($session['last_activity'] ?? 0);
    $timeout = max(300, (int) config('admin.session_timeout', 1800));
    $fingerprint = (string) ($session['fingerprint'] ?? '');

    if ($lastActivity < time() - $timeout || !hash_equals($fingerprint, admin_session_fingerprint())) {
        admin_logout();
        return false;
    }

    $_SESSION['admin']['last_activity'] = time();

    return true;
}

function admin_rate_limit_file(): string
{
    $key = (string) config('admin.password_hash', 'atlantic-admin');
    $identity = client_ip();
    $hash = hash_hmac('sha256', $identity, $key);

    return BASE_PATH . '/storage/admin-login-' . $hash . '.json';
}

function admin_rate_limit_attempts(): array
{
    $file = admin_rate_limit_file();

    if (!is_file($file)) {
        return [];
    }

    $contents = file_get_contents($file);
    $decoded = is_string($contents) ? json_decode($contents, true) : null;
    $window = max(60, (int) config('admin.login_window', 900));
    $threshold = time() - $window;

    if (!is_array($decoded)) {
        return [];
    }

    return array_values(array_filter(
        $decoded,
        static fn (mixed $timestamp): bool => is_int($timestamp) && $timestamp > $threshold
    ));
}

function admin_login_is_locked(): bool
{
    $maximum = max(1, (int) config('admin.login_max_attempts', 5));

    return count(admin_rate_limit_attempts()) >= $maximum;
}

function admin_record_failed_login(): void
{
    $file = admin_rate_limit_file();
    $directory = dirname($file);

    if (!is_dir($directory) || !is_writable($directory)) {
        enforce_rate_limit('admin_login_fallback', 5, 900);
        return;
    }

    $handle = fopen($file, 'c+');

    if ($handle === false) {
        enforce_rate_limit('admin_login_fallback', 5, 900);
        return;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            enforce_rate_limit('admin_login_fallback', 5, 900);
            return;
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        $decoded = is_string($contents) ? json_decode($contents, true) : null;
        $attempts = is_array($decoded) ? $decoded : [];
        $window = max(60, (int) config('admin.login_window', 900));
        $threshold = time() - $window;
        $attempts = array_values(array_filter(
            $attempts,
            static fn (mixed $timestamp): bool => is_int($timestamp) && $timestamp > $threshold
        ));
        $attempts[] = time();
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($attempts, JSON_THROW_ON_ERROR));
        fflush($handle);
        flock($handle, LOCK_UN);
    } finally {
        fclose($handle);
    }
}

function admin_clear_failed_logins(): void
{
    $file = admin_rate_limit_file();

    if (is_file($file)) {
        @unlink($file);
    }
}

function admin_attempt_login(string $username, string $password): string
{
    if (!admin_is_configured()) {
        return ADMIN_LOGIN_DISABLED;
    }

    if (admin_login_is_locked()) {
        return ADMIN_LOGIN_LOCKED;
    }

    $expectedUsername = (string) config('admin.username', '');
    $hash = (string) config('admin.password_hash', '');
    $usernameValid = hash_equals($expectedUsername, trim($username));
    $passwordValid = password_verify($password, $hash);

    if (!$usernameValid || !$passwordValid) {
        admin_record_failed_login();
        return admin_login_is_locked() ? ADMIN_LOGIN_LOCKED : ADMIN_LOGIN_INVALID;
    }

    admin_clear_failed_logins();
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
    $_SESSION['admin'] = [
        'authenticated' => true,
        'username' => $expectedUsername,
        'authenticated_at' => time(),
        'last_activity' => time(),
        'fingerprint' => admin_session_fingerprint(),
    ];

    return ADMIN_LOGIN_SUCCESS;
}

function admin_logout(): void
{
    unset($_SESSION['admin'], $_SESSION['csrf_token']);
    session_regenerate_id(true);
}

function require_admin(): void
{
    if (!admin_is_authenticated()) {
        http_response_code(403);
        exit(t('validation.admin_required'));
    }
}
