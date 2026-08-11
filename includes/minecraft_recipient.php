<?php

declare(strict_types=1);

function normalize_minecraft_platform(string $platform): string
{
    $platform = strtolower(trim($platform));

    if (!in_array($platform, ['java', 'bedrock'], true)) {
        throw new InvalidArgumentException(t('validation.platform'));
    }

    return $platform;
}

function normalize_minecraft_username(string $username, string $platform): string
{
    $username = trim($username);
    $platform = normalize_minecraft_platform($platform);

    if ($platform === 'java') {
        if (!preg_match('/^[A-Za-z0-9_]{3,16}$/', $username)) {
            throw new InvalidArgumentException(t('validation.java_username'));
        }

        return $username;
    }

    if (!preg_match('/^[A-Za-z0-9_ ]{2,16}$/', $username)) {
        throw new InvalidArgumentException(t('validation.bedrock_username'));
    }

    return preg_replace('/ +/', ' ', $username) ?? $username;
}

function minecraft_server_username(string $username, string $platform): string
{
    $platform = normalize_minecraft_platform($platform);
    $username = normalize_minecraft_username($username, $platform);

    if ($platform === 'java') {
        return $username;
    }

    $prefix = (string) config('app.bedrock_username_prefix', '');

    return $prefix . str_replace(' ', '_', $username);
}

function minecraft_avatar_url(string $username, int $size = 64): string
{
    $size = max(16, min(256, $size));

    return 'https://mc-heads.net/avatar/' . rawurlencode($username) . '/' . $size;
}

function minecraft_recipient_avatar_url(string $username, string $platform): string
{
    $platform = normalize_minecraft_platform($platform);

    if ($platform === 'bedrock') {
        return url('assets/steve.png');
    }

    return minecraft_avatar_url($username);
}

function current_minecraft_recipient(): ?array
{
    $recipient = $_SESSION['minecraft_recipient'] ?? null;

    if (!is_array($recipient)) {
        return null;
    }

    $username = $recipient['username'] ?? null;
    $platform = $recipient['platform'] ?? null;

    if (!is_string($username) || !is_string($platform)) {
        return null;
    }

    try {
        $platform = normalize_minecraft_platform($platform);
        $username = normalize_minecraft_username($username, $platform);
    } catch (InvalidArgumentException) {
        return null;
    }

    return [
        'username' => $username,
        'platform' => $platform,
        'server_username' => minecraft_server_username($username, $platform),
        'avatar_url' => minecraft_recipient_avatar_url($username, $platform),
    ];
}

function has_minecraft_recipient(): bool
{
    return current_minecraft_recipient() !== null;
}

function select_minecraft_recipient(string $username, string $platform): array
{
    $platform = normalize_minecraft_platform($platform);
    $normalizedUsername = normalize_minecraft_username($username, $platform);

    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);

    $recipient = [
        'username' => $normalizedUsername,
        'platform' => $platform,
        'server_username' => minecraft_server_username($normalizedUsername, $platform),
        'avatar_url' => minecraft_recipient_avatar_url($normalizedUsername, $platform),
    ];

    $_SESSION['minecraft_recipient'] = $recipient;

    return $recipient;
}

function clear_minecraft_recipient(): void
{
    unset(
        $_SESSION['minecraft_recipient'],
        $_SESSION['recipient_return_to'],
        $_SESSION['csrf_token']
    );

    session_regenerate_id(true);
}

function require_minecraft_recipient(string $returnTo = 'checkout'): void
{
    if (has_minecraft_recipient()) {
        return;
    }

    $_SESSION['recipient_return_to'] = safe_return_path(
        $returnTo,
        route_path('home')
    );

    flash('info', t('messages.recipient_required'));
    redirect_route('login');
}
