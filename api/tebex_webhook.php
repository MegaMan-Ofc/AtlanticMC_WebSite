<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
define('ATLANTIC_STATELESS', true);
require_once __DIR__ . '/../includes/bootstrap.php';

if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
    json_response(['error' => 'Method Not Allowed'], 405);
}

if (!tebex_webhook_ip_allowed(client_ip())) {
    json_response(['error' => 'Not Found'], 404);
}

$rawBody = file_get_contents('php://input');
$signature = (string) ($_SERVER['HTTP_X_SIGNATURE'] ?? '');

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
    error_log('Rejected Tebex webhook because products, total or currency did not match order ' . $orderToken);
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
