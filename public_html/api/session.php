<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_get();

$recipient = current_minecraft_recipient();
json_response(['data' => [
    'recipient_selected' => $recipient !== null,
    'recipient' => $recipient,
    'cart_count' => cart_count(),
    'language' => current_language(),
]]);
