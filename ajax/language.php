<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();
enforce_rate_limit('ajax_language', 30, 60);

try {
    set_language(request_string('language'));

    json_response([
        'message' => t('language.updated'),
        'data' => [
            'language' => current_language(),
            'label' => language_label(),
            'reload' => true,
        ],
    ]);
} catch (InvalidArgumentException $error) {
    json_response(['error' => $error->getMessage()], 422);
}
