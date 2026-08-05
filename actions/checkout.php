<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
enforce_rate_limit('checkout', 10, 60);
require_minecraft_recipient('checkout.php');

try {
    $checkout = start_checkout();

    if ($checkout['message'] !== null) {
        flash('info', $checkout['message']);
    }

    if ($checkout['external']) {
        redirect_external($checkout['redirect_url']);
    }

    redirect($checkout['redirect_url']);
} catch (Throwable $error) {
    flash('error', public_error_message($error, 'Não foi possível iniciar o pagamento. Tenta novamente.'));
    redirect('checkout.php');
}
