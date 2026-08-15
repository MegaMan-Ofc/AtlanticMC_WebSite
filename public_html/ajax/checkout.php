<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_post();
verify_csrf();
enforce_rate_limit('checkout', 10, 60);

try {
    if (current_minecraft_recipient() === null) {
        json_response([
            'error' => t('messages.recipient_required_first'),
            'data' => ['redirect_url' => route_url('login', ['return_to' => route_path('checkout')])],
        ], 401);
    }

    $checkout = start_checkout();

    if ($checkout['message'] !== null) {
        flash('info', $checkout['message']);
    }

    json_response([
        'message' => $checkout['message'],
        'data' => [
            'redirect_url' => $checkout['external']
                ? $checkout['redirect_url']
                : url($checkout['redirect_url']),
            'external' => $checkout['external'],
        ],
    ]);
} catch (InvalidArgumentException $error) {
    json_response(['error' => $error->getMessage()], 422);
} catch (Throwable $error) {
    json_response([
        'error' => public_error_message(
            $error,
            t('messages.payment_failed')
        ),
    ], 500);
}
