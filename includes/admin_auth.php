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

    $now = time();
    $lastActivity = (int) ($session['last_activity'] ?? 0);
    $timeout = max(300, (int) config('admin.session_timeout', 1800));
    $fingerprint = (string) ($session['fingerprint'] ?? '');

    if ($lastActivity < $now - $timeout || !hash_equals($fingerprint, admin_session_fingerprint())) {
        admin_logout();
        return false;
    }

    $regeneratedAt = (int) ($session['regenerated_at'] ?? 0);

    if ($regeneratedAt < $now - 900) {
        session_regenerate_id(false);
        $_SESSION['admin']['regenerated_at'] = $now;
    }

    $_SESSION['admin']['last_activity'] = $now;

    return true;
}

function admin_login_identity(string $username): string
{
    $secret = (string) config('app.key', 'atlantic-development-key');
    $identity = client_ip() . '|' . strtolower(trim($username));

    return hash_hmac('sha256', $identity, $secret);
}

function admin_login_limit_row(string $identity): ?array
{
    $statement = db()->prepare(
        'SELECT attempts, window_started_at, locked_until
         FROM admin_login_limits
         WHERE identity_hash = :identity_hash'
    );
    $statement->execute(['identity_hash' => $identity]);
    $row = $statement->fetch();

    return is_array($row) ? $row : null;
}

function admin_login_is_locked(string $username): bool
{
    try {
        $row = admin_login_limit_row(admin_login_identity($username));
    } catch (Throwable $error) {
        security_log('error', 'admin_rate_limit_read_failed', ['message' => $error->getMessage()]);
        return is_production();
    }

    if ($row === null || empty($row['locked_until'])) {
        return false;
    }

    return strtotime((string) $row['locked_until']) > time();
}

function admin_record_failed_login(string $username): void
{
    $pdo = db();
    $driver = (string) config('database.driver');
    $identity = admin_login_identity($username);
    $window = max(60, (int) config('admin.login_window', 900));
    $maximum = max(1, (int) config('admin.login_max_attempts', 5));
    $now = time();
    $started = false;

    try {
        if ($driver === 'sqlite') {
            $pdo->exec('BEGIN IMMEDIATE');
            $started = true;
        } else {
            $pdo->beginTransaction();
            $started = true;
        }

        $sql = 'SELECT attempts, window_started_at, locked_until
                FROM admin_login_limits
                WHERE identity_hash = :identity_hash';

        if ($driver === 'mysql') {
            $sql .= ' FOR UPDATE';
        }

        $statement = $pdo->prepare($sql);
        $statement->execute(['identity_hash' => $identity]);
        $row = $statement->fetch();
        $windowStarted = is_array($row) ? strtotime((string) $row['window_started_at']) : false;
        $attempts = is_array($row) ? (int) $row['attempts'] : 0;

        if ($windowStarted === false || $windowStarted <= $now - $window) {
            $windowStarted = $now;
            $attempts = 0;
        }

        $attempts++;
        $lockedUntil = $attempts >= $maximum
            ? date('Y-m-d H:i:s', $windowStarted + $window)
            : null;
        $values = [
            'identity_hash' => $identity,
            'attempts' => $attempts,
            'window_started_at' => date('Y-m-d H:i:s', $windowStarted),
            'locked_until' => $lockedUntil,
            'updated_at' => now_sql(),
        ];

        if (is_array($row)) {
            $save = $pdo->prepare(
                'UPDATE admin_login_limits
                 SET attempts = :attempts,
                     window_started_at = :window_started_at,
                     locked_until = :locked_until,
                     updated_at = :updated_at
                 WHERE identity_hash = :identity_hash'
            );
        } else {
            $save = $pdo->prepare(
                'INSERT INTO admin_login_limits
                 (identity_hash, attempts, window_started_at, locked_until, updated_at)
                 VALUES
                 (:identity_hash, :attempts, :window_started_at, :locked_until, :updated_at)'
            );
        }

        $save->execute($values);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($started && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        security_log('error', 'admin_rate_limit_write_failed', ['message' => $error->getMessage()]);

        if (is_production()) {
            throw new RuntimeException('Administrator login is temporarily unavailable.', 0, $error);
        }

        enforce_rate_limit('admin_login_fallback', $maximum, $window);
    }
}

function admin_clear_failed_logins(string $username): void
{
    try {
        $statement = db()->prepare('DELETE FROM admin_login_limits WHERE identity_hash = :identity_hash');
        $statement->execute(['identity_hash' => admin_login_identity($username)]);
    } catch (Throwable $error) {
        security_log('warning', 'admin_rate_limit_cleanup_failed', ['message' => $error->getMessage()]);
    }
}

function admin_attempt_login(string $username, string $password): string
{
    if (!admin_is_configured()) {
        return ADMIN_LOGIN_DISABLED;
    }

    $username = trim($username);

    if (admin_login_is_locked($username)) {
        security_log('warning', 'admin_login_locked', ['ip_hash' => admin_login_identity(''), 'username_hash' => hash('sha256', strtolower($username))]);
        return ADMIN_LOGIN_LOCKED;
    }

    $expectedUsername = (string) config('admin.username', '');
    $hash = (string) config('admin.password_hash', '');
    $usernameValid = hash_equals($expectedUsername, $username);
    $passwordValid = password_verify($password, $hash);

    if (!$usernameValid || !$passwordValid) {
        admin_record_failed_login($username);
        security_log('warning', 'admin_login_failed', ['ip_hash' => admin_login_identity(''), 'username_hash' => hash('sha256', strtolower($username))]);
        return admin_login_is_locked($username) ? ADMIN_LOGIN_LOCKED : ADMIN_LOGIN_INVALID;
    }

    admin_clear_failed_logins($username);
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
    $now = time();
    security_log('info', 'admin_login_succeeded', ['username_hash' => hash('sha256', strtolower($expectedUsername))]);
    $_SESSION['admin'] = [
        'authenticated' => true,
        'username' => $expectedUsername,
        'authenticated_at' => $now,
        'last_activity' => $now,
        'regenerated_at' => $now,
        'fingerprint' => admin_session_fingerprint(),
    ];

    return ADMIN_LOGIN_SUCCESS;
}

function admin_logout(): void
{
    if (isset($_SESSION['admin'])) {
        security_log('info', 'admin_logout');
    }

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
