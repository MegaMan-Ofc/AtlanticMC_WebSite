<?php

declare(strict_types=1);

function current_minecraft_recipient(): ?array
{
    $recipient = $_SESSION['minecraft_recipient'] ?? null;

    if (!is_array($recipient)) {
        return null;
    }

    $username = $recipient['username'] ?? null;
    $platform = $recipient['platform'] ?? null;
    $avatarUrl = $recipient['avatar_url'] ?? null;

    if (!is_string($username) || !is_string($platform) || !is_string($avatarUrl)) {
        return null;
    }

    return [
        'username' => $username,
        'platform' => $platform,
        'avatar_url' => $avatarUrl,
    ];
}

function has_minecraft_recipient(): bool
{
    return current_minecraft_recipient() !== null;
}

function normalize_minecraft_username(string $username, string $platform): string
{
    $username = trim($username);
    $platform = strtolower(trim($platform));

    if (!in_array($platform, ['java', 'bedrock'], true)) {
        throw new InvalidArgumentException(t('validation.platform'));
    }

    if ($platform === 'bedrock') {
        $cleanUsername = ltrim($username, '.');

        if (!preg_match('/^[A-Za-z0-9_]{2,15}$/', $cleanUsername)) {
            throw new InvalidArgumentException(
                t('validation.bedrock_username')
            );
        }

        return '.' . $cleanUsername;
    }

    if (!preg_match('/^[A-Za-z0-9_]{3,16}$/', $username)) {
        throw new InvalidArgumentException(
            t('validation.java_username')
        );
    }

    return $username;
}

function minecraft_avatar_url(string $username, int $size = 64): string
{
    $cleanUsername = ltrim($username, '.');
    $size = max(16, min(256, $size));

    return 'https://mc-heads.net/avatar/' . rawurlencode($cleanUsername) . '/' . $size;
}

function select_minecraft_recipient(string $username, string $platform): array
{
    $platform = strtolower(trim($platform));
    $normalizedUsername = normalize_minecraft_username($username, $platform);

    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);

    $recipient = [
        'username' => $normalizedUsername,
        'platform' => $platform,
        'avatar_url' => minecraft_avatar_url($normalizedUsername),
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

function require_minecraft_recipient(string $returnTo = 'checkout.php'): void
{
    if (has_minecraft_recipient()) {
        return;
    }

    $_SESSION['recipient_return_to'] = safe_return_path($returnTo, 'index.php');
    flash('info', t('messages.recipient_required'));
    redirect('login.php');
}
