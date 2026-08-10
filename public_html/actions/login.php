<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_post();
verify_csrf();
enforce_rate_limit('minecraft_recipient', 30, 300);

try {
    $username = request_string('username');
    $platform = request_string('platform', 'java');
    $recipient = select_minecraft_recipient($username, $platform);
    $returnTo = safe_return_path(
        request_string('return_to', (string) ($_SESSION['recipient_return_to'] ?? route_path('home'))),
        route_path('home')
    );

    unset($_SESSION['recipient_return_to']);
    flash('success', t('messages.recipient_selected', ['username' => $recipient['username']]));
    redirect($returnTo);
} catch (InvalidArgumentException $error) {
    flash('error', $error->getMessage());
    redirect_route('login');
}
