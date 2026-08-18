<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_post();
verify_csrf();
require_admin();

try {
    $settings = save_admin_home_banner_customization(
        request_int('category_id'),
        [
            'kicker' => request_string('kicker'),
            'title' => request_string('title'),
            'text' => request_string('text'),
            'cta' => request_string('cta'),
            'style' => request_string('style', 'auto'),
            'image_side' => request_string('image_side', 'right'),
            'image_size' => request_string('image_size', 'normal'),
            'show_watermark' => request_int('show_watermark') === 1,
            'show_cta' => request_int('show_cta') === 1,
        ]
    );

    json_response([
        'ok' => true,
        'category_id' => request_int('category_id'),
        'settings' => $settings,
        'message' => t('admin.home_banner_saved'),
    ]);
} catch (InvalidArgumentException $error) {
    json_response(['error' => $error->getMessage()], 422);
} catch (Throwable $error) {
    json_response(['error' => public_error_message($error, t('messages.admin_save_failed'))], 500);
}
