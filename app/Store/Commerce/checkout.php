<?php

declare(strict_types=1);

function start_checkout(): array
{
    $summary = cart_summary();

    if ($summary['items'] === []) {
        throw new InvalidArgumentException(t('messages.cart_empty'));
    }

    $recipient = current_minecraft_recipient();

    if ($recipient === null) {
        throw new RuntimeException(t('messages.recipient_required_first'));
    }

    if (!tebex_is_configured() && !(bool) config('app.allow_test_orders', false)) {
        throw new RuntimeException(t('messages.payment_unavailable'));
    }

    $order = create_order($recipient, $summary);
    $orderToken = (string) $order['public_token'];
    $_SESSION['last_order_token'] = $orderToken;

    if (tebex_is_configured()) {
        try {
            $tebex = tebex_create_checkout($order, $summary['items']);
            update_order_provider(
                (int) $order['id'],
                (string) $tebex['reference'],
                (string) $tebex['checkout_url'],
                (int) $tebex['total_cents'],
                (string) $tebex['currency']
            );
        } catch (Throwable $error) {
            mark_order_status_by_token($orderToken, 'checkout_failed');
            throw $error;
        }

        cart_clear();

        return [
            'redirect_url' => $tebex['checkout_url'],
            'external' => true,
            'message' => null,
            'order_token' => $orderToken,
        ];
    }

    cart_clear();

    return [
        'redirect_url' => route_path('success', ['order' => $orderToken]),
        'external' => false,
        'message' => t('messages.test_order_created'),
        'order_token' => $orderToken,
    ];
}
