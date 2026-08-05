<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
enforce_rate_limit('checkout', 10, 60);

try {
    if (current_minecraft_recipient() === null) {
        json_response([
            'error' => 'Escolhe primeiro a conta Minecraft que vai receber a compra.',
            'data' => ['redirect_url' => url('login.php?return_to=checkout.php')],
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
            'Não foi possível iniciar o pagamento. Tenta novamente.'
        ),
    ], 500);
}
