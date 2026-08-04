<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
enforce_rate_limit('minecraft_login', 10, 600);

try {
    redirect_external(minecraft_authorization_url());
} catch (RuntimeException $error) {
    flash('error', public_error_message($error, 'O login Minecraft não está disponível neste momento.'));
    redirect('login.php');
}
