<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once __DIR__ . '/../includes/bootstrap.php';
require_get();

$user = current_user();
json_response(['data' => [
    'authenticated' => $user !== null,
    'user' => $user === null ? null : [
        'minecraft_uuid' => $user['minecraft_uuid'],
        'minecraft_name' => $user['minecraft_name'],
        'avatar_url' => $user['avatar_url'],
    ],
    'cart_count' => cart_count(),
]]);
