<?php

declare(strict_types=1);

$layout = admin_home_category_layout();
$allIds = array_map(
    static fn (array $category): int => (int) $category['id'],
    all_store_categories(true)
);

$gridIds = array_map(
    static fn (array $category): int => (int) $category['id'],
    $layout['grid']
);

$topId = is_array($layout['top']) ? (int) $layout['top']['id'] : 0;
$bottomId = is_array($layout['bottom']) ? (int) $layout['bottom']['id'] : 0;
$orderedIds = $gridIds;

if ($topId > 0) {
    array_unshift($orderedIds, $topId);
}

if ($bottomId > 0) {
    $orderedIds[] = $bottomId;
}

$assert(
    count($orderedIds) === count($allIds)
        && count(array_unique($orderedIds)) === count($allIds),
    'Every category appears exactly once in the managed homepage layout.'
);

if (count($allIds) >= 3) {
    $newTopId = $allIds[0];
    $newBottomId = $allIds[count($allIds) - 1];
    $newGridIds = array_values(array_filter(
        $allIds,
        static fn (int $id): bool => $id !== $newTopId && $id !== $newBottomId
    ));
    $newGridIds = array_reverse($newGridIds);

    save_admin_home_category_layout($newTopId, $newGridIds, $newBottomId);
    $savedLayout = admin_home_category_layout();

    $assert(
        is_array($savedLayout['top'])
            && (int) $savedLayout['top']['id'] === $newTopId
            && is_array($savedLayout['bottom'])
            && (int) $savedLayout['bottom']['id'] === $newBottomId
            && array_map(
                static fn (array $category): int => (int) $category['id'],
                $savedLayout['grid']
            ) === $newGridIds,
        'Administrators can place one category in each banner and reorder every remaining category in the middle grid.'
    );

    $throws(
        static fn () => save_admin_home_category_layout($newTopId, $newGridIds, $newTopId),
        'Homepage category layout rejects duplicated or missing categories.'
    );
}
