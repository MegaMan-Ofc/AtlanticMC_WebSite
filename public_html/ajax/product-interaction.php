<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_post();
verify_csrf();
enforce_rate_limit('product_interaction', 240, 60);

$productId = request_int('product_id');

if ($productId < 1 || product_by_id($productId) === null) {
    json_response(['error' => t('validation.product_not_found')], 404);
}

track_product_interaction($productId);
json_response(['ok' => true]);
