<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_post();
verify_csrf();

try {
    set_language(request_string('language'));
    flash('success', t('language.updated'));
} catch (InvalidArgumentException $error) {
    flash('error', $error->getMessage());
}

redirect(safe_return_path(request_string('return_to'), route_path('home')));
