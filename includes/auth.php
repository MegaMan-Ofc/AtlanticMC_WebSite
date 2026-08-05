<?php

declare(strict_types=1);

function admin_is_authenticated(): bool
{
    return ($_SESSION['admin_authenticated'] ?? false) === true;
}

function admin_login(string $username, string $password): bool
{
    $expectedUsername = (string) config('admin.username', 'admin');
    $hash = (string) config('admin.password_hash', '');

    if ($hash === '' || !hash_equals($expectedUsername, $username) || !password_verify($password, $hash)) {
        return false;
    }

    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
    $_SESSION['admin_authenticated'] = true;

    return true;
}

function admin_logout(): void
{
    unset($_SESSION['admin_authenticated'], $_SESSION['csrf_token']);
    session_regenerate_id(true);
}

function require_admin(): void
{
    if (!admin_is_authenticated()) {
        http_response_code(403);
        exit(t('validation.admin_required'));
    }
}
