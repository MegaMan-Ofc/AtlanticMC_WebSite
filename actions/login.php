<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
enforce_rate_limit('minecraft_recipient', 30, 300);

try {
    $username = request_string('username');
    $platform = request_string('platform', 'java');
    $recipient = select_minecraft_recipient($username, $platform);
    $returnTo = safe_return_path(
        request_string('return_to', (string) ($_SESSION['recipient_return_to'] ?? 'index.php')),
        'index.php'
    );

    unset($_SESSION['recipient_return_to']);
    flash('success', 'Compra associada a ' . $recipient['username'] . '.');
    redirect($returnTo);
} catch (InvalidArgumentException $error) {
    flash('error', $error->getMessage());
    redirect('login.php');
}
