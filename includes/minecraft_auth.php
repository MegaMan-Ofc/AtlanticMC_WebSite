<?php

declare(strict_types=1);

function minecraft_authorization_url(): string
{
    if (!minecraft_login_configured()) {
        throw new RuntimeException('Minecraft login is not configured.');
    }

    $state = random_token(24);
    $verifier = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
    $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

    $_SESSION['minecraft_oauth'] = [
        'state' => $state,
        'verifier' => $verifier,
        'created_at' => time(),
    ];

    $query = http_build_query([
        'client_id' => config('minecraft.client_id'),
        'response_type' => 'code',
        'redirect_uri' => config('minecraft.redirect_uri'),
        'response_mode' => 'query',
        'scope' => 'XboxLive.signin XboxLive.offline_access',
        'state' => $state,
        'code_challenge' => $challenge,
        'code_challenge_method' => 'S256',
        'prompt' => 'select_account',
    ], '', '&', PHP_QUERY_RFC3986);

    return 'https://login.microsoftonline.com/consumers/oauth2/v2.0/authorize?' . $query;
}

function authenticate_minecraft_callback(string $code, string $state): array
{
    $oauth = $_SESSION['minecraft_oauth'] ?? null;
    unset($_SESSION['minecraft_oauth']);

    if (!is_array($oauth) || !isset($oauth['state'], $oauth['verifier'], $oauth['created_at'])) {
        throw new RuntimeException('Authentication session expired.');
    }

    if ((time() - (int) $oauth['created_at']) > 600 || !hash_equals((string) $oauth['state'], $state)) {
        throw new RuntimeException('Invalid Microsoft authentication state.');
    }

    $microsoftToken = http_post_form(
        'https://login.microsoftonline.com/consumers/oauth2/v2.0/token',
        [
            'client_id' => config('minecraft.client_id'),
            'client_secret' => config('minecraft.client_secret'),
            'code' => $code,
            'redirect_uri' => config('minecraft.redirect_uri'),
            'grant_type' => 'authorization_code',
            'scope' => 'XboxLive.signin XboxLive.offline_access',
            'code_verifier' => $oauth['verifier'],
        ]
    );

    $accessToken = (string) ($microsoftToken['access_token'] ?? '');

    if ($accessToken === '') {
        throw new RuntimeException('Microsoft did not return an access token.');
    }

    $xboxUser = http_request_json(
        'POST',
        'https://user.auth.xboxlive.com/user/authenticate',
        [
            'RelyingParty' => 'http://auth.xboxlive.com',
            'TokenType' => 'JWT',
            'Properties' => [
                'AuthMethod' => 'RPS',
                'SiteName' => 'user.auth.xboxlive.com',
                'RpsTicket' => 'd=' . $accessToken,
            ],
        ],
        ['x-xbl-contract-version: 1']
    );

    $userToken = (string) ($xboxUser['Token'] ?? '');

    if ($userToken === '') {
        throw new RuntimeException('Xbox Live did not return a user token.');
    }

    $xsts = http_request_json(
        'POST',
        'https://xsts.auth.xboxlive.com/xsts/authorize',
        [
            'Properties' => [
                'SandboxId' => 'RETAIL',
                'UserTokens' => [$userToken],
            ],
            'RelyingParty' => 'rp://api.minecraftservices.com/',
            'TokenType' => 'JWT',
        ],
        ['x-xbl-contract-version: 1']
    );

    $xstsToken = (string) ($xsts['Token'] ?? '');
    $userHash = (string) ($xsts['DisplayClaims']['xui'][0]['uhs'] ?? '');

    if ($xstsToken === '' || $userHash === '') {
        throw new RuntimeException('Xbox Live did not return the required Minecraft claims.');
    }

    $minecraftLogin = http_request_json(
        'POST',
        'https://api.minecraftservices.com/authentication/login_with_xbox',
        ['identityToken' => 'XBL3.0 x=' . $userHash . ';' . $xstsToken]
    );

    $minecraftToken = (string) ($minecraftLogin['access_token'] ?? '');

    if ($minecraftToken === '') {
        throw new RuntimeException('Minecraft Services did not return an access token.');
    }

    return http_request_json(
        'GET',
        'https://api.minecraftservices.com/minecraft/profile',
        [],
        ['Authorization: Bearer ' . $minecraftToken]
    );
}
