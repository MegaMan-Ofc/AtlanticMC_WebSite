<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
clear_minecraft_recipient();
flash('success', t('messages.recipient_removed'));
redirect_route('login');
