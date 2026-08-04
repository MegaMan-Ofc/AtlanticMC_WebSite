<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
enforce_rate_limit('checkout', 10, 60);
require_authentication('checkout.php');

try {
    $summary = cart_summary();

    if ($summary['items'] === []) {
        throw new InvalidArgumentException('O carrinho está vazio.');
    }

    $user = current_user() ?? throw new RuntimeException('Authentication required.');

    if (!tebex_is_configured() && !(bool) config('app.allow_test_orders', false)) {
        throw new RuntimeException('Payments are temporarily unavailable.');
    }

    $order = create_order($user, $summary);
    $_SESSION['last_order_token'] = $order['public_token'];

    if (tebex_is_configured()) {
        try {
            $tebex = tebex_create_checkout($order, $summary['items']);
            update_order_provider((int) $order['id'], $tebex['reference'], $tebex['checkout_url']);
        } catch (Throwable $error) {
            mark_order_status_by_token((string) $order['public_token'], 'checkout_failed');
            throw $error;
        }

        cart_clear();
        redirect_external($tebex['checkout_url']);
    }

    cart_clear();
    flash('info', 'Pedido local criado. Nenhum pagamento foi efetuado porque o modo de teste está ativo.');
    redirect('success.php?order=' . rawurlencode((string) $order['public_token']));
} catch (Throwable $error) {
    flash('error', public_error_message($error, 'Não foi possível iniciar o pagamento. Tenta novamente.'));
    redirect('checkout.php');
}
