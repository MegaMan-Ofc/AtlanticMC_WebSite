<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
define('ATLANTIC_STATELESS', true);
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

if (!(bool) config('app.payments_enabled', false)) {
    json_response(['error' => 'Not Found'], 404);
}

if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
    json_response(['error' => 'Method Not Allowed'], 405);
}

if (!tebex_webhook_ip_allowed(client_ip())) {
    json_response(['error' => 'Not Found'], 404);
}

$contentType = strtolower(trim(explode(';', (string) ($_SERVER['CONTENT_TYPE'] ?? ''))[0] ?? ''));

if ($contentType !== 'application/json') {
    json_response(['error' => 'Unsupported Media Type'], 415);
}

$maximumBodySize = 1_048_576;
$contentLength = filter_var($_SERVER['CONTENT_LENGTH'] ?? null, FILTER_VALIDATE_INT);

if ($contentLength !== false && $contentLength > $maximumBodySize) {
    json_response(['error' => 'Payload Too Large'], 413);
}

$rawBody = file_get_contents('php://input', false, null, 0, $maximumBodySize + 1);
$signature = (string) ($_SERVER['HTTP_X_SIGNATURE'] ?? '');

if (is_string($rawBody) && strlen($rawBody) > $maximumBodySize) {
    json_response(['error' => 'Payload Too Large'], 413);
}

if (!is_string($rawBody) || !verify_tebex_webhook_signature($rawBody, $signature)) {
    json_response(['error' => 'Invalid signature'], 401);
}

try {
    $event = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    json_response(['error' => 'Invalid JSON'], 400);
}

if (!is_array($event)) {
    json_response(['error' => 'Invalid event'], 422);
}

$eventId = trim((string) ($event['id'] ?? ''));
$eventType = trim((string) ($event['type'] ?? ''));

if ($eventId === '' || $eventType === '') {
    json_response(['error' => 'Invalid event'], 422);
}

if ($eventType === 'validation.webhook') {
    json_response(['id' => $eventId]);
}

$subject = is_array($event['subject'] ?? null) ? $event['subject'] : [];
$custom = is_array($subject['custom'] ?? null) ? $subject['custom'] : [];
$orderToken = trim((string) ($custom['order_token'] ?? ''));
$reference = trim((string) ($subject['transaction_id'] ?? $subject['id'] ?? ''));

if ($orderToken === '') {
    json_response(['error' => 'Order token missing'], 422);
}

$order = order_by_token($orderToken);

if ($order === null) {
    json_response(['error' => 'Order not found'], 404);
}

if ($eventType === 'payment.completed' && !tebex_webhook_matches_order($subject, $order)) {
    security_log('warning', 'tebex_webhook_order_mismatch', ['order_id' => (int) $order['id'], 'event_id' => $eventId]);
    json_response(['error' => 'Payment does not match order'], 422);
}

$status = match ($eventType) {
    'payment.completed' => 'paid',
    'payment.declined' => 'declined',
    'payment.refunded' => 'refunded',
    'payment.dispute.opened', 'payment.dispute.lost' => 'disputed',
    default => null,
};

$processed = $status === null
    ? record_webhook_event('tebex', $eventId, $eventType)
    : process_order_webhook(
        'tebex',
        $eventId,
        $eventType,
        $orderToken,
        $status,
        $reference !== '' ? $reference : null
    );

if (!$processed) {
    json_response(['ok' => true, 'duplicate' => true]);
}

json_response(['ok' => true]);
