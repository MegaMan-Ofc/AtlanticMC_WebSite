<?php

declare(strict_types=1);

define('ATLANTIC_JSON', true);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_post();
verify_csrf();
require_admin();

try {
    $topCategoryId = max(0, request_int('top_category_id'));
    $bottomCategoryId = max(0, request_int('bottom_category_id'));
    $rawGrid = trim(request_string('grid_category_ids'));
    $gridCategoryIds = $rawGrid === ''
        ? []
        : array_values(array_filter(
            array_map('intval', explode(',', $rawGrid)),
            static fn (int $id): bool => $id > 0
        ));

    save_admin_home_category_layout($topCategoryId, $gridCategoryIds, $bottomCategoryId);
    json_response(['ok' => true]);
} catch (InvalidArgumentException $error) {
    json_response(['error' => $error->getMessage()], 422);
} catch (Throwable $error) {
    json_response(['error' => public_error_message($error, t('messages.admin_save_failed'))], 500);
}
