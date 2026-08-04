<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$error = is_string($_GET['error'] ?? null) ? $_GET['error'] : '';
$description = is_string($_GET['error_description'] ?? null) ? $_GET['error_description'] : '';

if ($error !== '') {
    flash('error', $description !== '' ? $description : 'Microsoft login was cancelled.');
    redirect('login.php');
}

$code = is_string($_GET['code'] ?? null) ? $_GET['code'] : '';
$state = is_string($_GET['state'] ?? null) ? $_GET['state'] : '';

try {
    if ($code === '' || $state === '') {
        throw new RuntimeException('Microsoft did not return the required login data.');
    }

    $profile = authenticate_minecraft_callback($code, $state);
    $user = upsert_minecraft_user((string) ($profile['id'] ?? ''), (string) ($profile['name'] ?? ''));
    login_minecraft_user($user);
    flash('success', 'Sessão iniciada como ' . $user['minecraft_name'] . '.');
    $returnTo = safe_return_path($_SESSION['login_return_to'] ?? null, 'index.php');
    unset($_SESSION['login_return_to']);
    redirect($returnTo);
} catch (Throwable $error) {
    flash('error', public_error_message($error, 'Não foi possível validar a conta Minecraft. Tenta novamente.'));
    redirect('login.php');
}
