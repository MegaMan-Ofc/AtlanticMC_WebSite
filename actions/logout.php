<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
logout_user();
flash('success', 'Sessão terminada.');
redirect('index.php');
