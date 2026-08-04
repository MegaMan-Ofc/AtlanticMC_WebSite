<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Atlantic Anarchy - Login';
$pageDescription = 'Sign in securely with the Microsoft account that owns Minecraft.';
$bodyClass = 'page-login';
$pageStyles = ['css/pages/auth.css'];
$loginConfigured = minecraft_login_configured();
$loginUser = current_user();
