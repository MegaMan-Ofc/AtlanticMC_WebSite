<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
admin_logout();
flash('success', 'Administrator session ended.');
redirect('admin.php');
