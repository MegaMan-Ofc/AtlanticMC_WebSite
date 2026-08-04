<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
enforce_rate_limit('admin_login', 5, 300);

if (admin_login(request_string('username'), request_string('password'))) {
    flash('success', 'Administrator session started.');
} else {
    flash('error', 'Invalid administrator credentials.');
}

redirect('admin.php');
