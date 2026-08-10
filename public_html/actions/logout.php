<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_post();
verify_csrf();
clear_minecraft_recipient();
flash('success', t('messages.recipient_removed'));
redirect_route('login');
