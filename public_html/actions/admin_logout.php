<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_post();
verify_csrf();
admin_logout();
flash('success', t('messages.admin_session_ended'));
redirect_route('admin');
