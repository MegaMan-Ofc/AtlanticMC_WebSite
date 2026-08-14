<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_post();
verify_csrf();
require_admin();

try {
    $raw = request_string('product_ids');
    $productIds = array_map('intval', explode(',', $raw));
    reorder_recommended_products($productIds);
    json_response(['ok' => true]);
} catch (InvalidArgumentException $error) {
    json_response(['error' => $error->getMessage()], 422);
} catch (Throwable $error) {
    json_response(['error' => public_error_message($error, t('messages.admin_save_failed'))], 500);
}
