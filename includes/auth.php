<?php

declare(strict_types=1);

function current_user(): ?array
{
    $user = $_SESSION['user'] ?? null;

    return is_array($user) ? $user : null;
}

function is_authenticated(): bool
{
    return current_user() !== null;
}

function require_authentication(string $returnTo = 'checkout.php'): void
{
    if (is_authenticated()) {
        return;
    }

    $_SESSION['login_return_to'] = safe_return_path($returnTo, 'index.php');
    flash('info', 'Inicia sessão com a tua conta Microsoft/Minecraft para continuar.');
    redirect('login.php');
}

function upsert_minecraft_user(string $uuid, string $name): array
{
    $uuid = strtolower(preg_replace('/[^a-f0-9]/i', '', $uuid) ?? '');

    if (!preg_match('/^[a-f0-9]{32}$/', $uuid)) {
        throw new InvalidArgumentException('Invalid Minecraft UUID.');
    }

    if (!preg_match('/^[A-Za-z0-9_]{1,16}$/', $name)) {
        throw new InvalidArgumentException('Invalid Minecraft profile name.');
    }

    $avatarUrl = 'https://mc-heads.net/avatar/' . rawurlencode($uuid) . '/64';
    $statement = db()->prepare('SELECT * FROM users WHERE minecraft_uuid = :uuid');
    $statement->execute(['uuid' => $uuid]);
    $existing = $statement->fetch();
    $now = now_sql();

    if (is_array($existing)) {
        $update = db()->prepare(
            'UPDATE users SET minecraft_name = :name, avatar_url = :avatar_url, last_login_at = :last_login_at WHERE id = :id'
        );
        $update->execute([
            'name' => $name,
            'avatar_url' => $avatarUrl,
            'last_login_at' => $now,
            'id' => $existing['id'],
        ]);
        $existing['minecraft_name'] = $name;
        $existing['avatar_url'] = $avatarUrl;
        $existing['last_login_at'] = $now;

        return $existing;
    }

    $insert = db()->prepare(
        'INSERT INTO users (minecraft_uuid, minecraft_name, avatar_url, created_at, last_login_at)
         VALUES (:uuid, :name, :avatar_url, :created_at, :last_login_at)'
    );
    $insert->execute([
        'uuid' => $uuid,
        'name' => $name,
        'avatar_url' => $avatarUrl,
        'created_at' => $now,
        'last_login_at' => $now,
    ]);

    return [
        'id' => (int) db()->lastInsertId(),
        'minecraft_uuid' => $uuid,
        'minecraft_name' => $name,
        'avatar_url' => $avatarUrl,
        'created_at' => $now,
        'last_login_at' => $now,
    ];
}

function login_minecraft_user(array $user): void
{
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'minecraft_uuid' => (string) $user['minecraft_uuid'],
        'minecraft_name' => (string) $user['minecraft_name'],
        'avatar_url' => (string) $user['avatar_url'],
    ];
}

function logout_user(): void
{
    unset($_SESSION['user'], $_SESSION['minecraft_oauth'], $_SESSION['csrf_token']);
    session_regenerate_id(true);
}

function minecraft_login_configured(): bool
{
    return config('minecraft.client_id', '') !== ''
        && config('minecraft.client_secret', '') !== ''
        && config('minecraft.redirect_uri', '') !== '';
}

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
        exit('Administrator authentication required.');
    }
}
