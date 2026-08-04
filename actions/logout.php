<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
clear_minecraft_recipient();
flash('success', 'Nome Minecraft removido da sessão.');
redirect('login.php');
