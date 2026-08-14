<?php

declare(strict_types=1);

function admin_home_category_layout(): array
{
    $categories = all_store_categories(true);

    usort($categories, static function (array $left, array $right): int {
        $sort = (int) ($left['home_sort_order'] ?? $left['sort_order']) <=> (int) ($right['home_sort_order'] ?? $right['sort_order']);

        return $sort !== 0 ? $sort : (int) $left['id'] <=> (int) $right['id'];
    });

    $layout = [
        'top' => null,
        'grid' => [],
        'bottom' => null,
    ];

    foreach ($categories as $category) {
        $placement = (string) ($category['home_placement'] ?? 'grid');

        if ($placement === 'top' && $layout['top'] === null) {
            $layout['top'] = $category;
            continue;
        }

        if ($placement === 'bottom' && $layout['bottom'] === null) {
            $layout['bottom'] = $category;
            continue;
        }

        $layout['grid'][] = $category;
    }

    return $layout;
}

function save_admin_home_category_layout(int $topCategoryId, array $gridCategoryIds, int $bottomCategoryId): void
{
    $allCategoryIds = array_map(
        static fn (array $category): int => (int) $category['id'],
        all_store_categories(true)
    );

    $gridCategoryIds = array_values(array_map('intval', $gridCategoryIds));
    $submitted = $gridCategoryIds;

    if ($topCategoryId > 0) {
        array_unshift($submitted, $topCategoryId);
    }

    if ($bottomCategoryId > 0) {
        $submitted[] = $bottomCategoryId;
    }

    if (
        count($submitted) !== count($allCategoryIds)
        || count($submitted) !== count(array_unique($submitted))
    ) {
        throw new InvalidArgumentException(t('validation.home_category_layout'));
    }

    $expected = $allCategoryIds;
    sort($expected);
    $actual = $submitted;
    sort($actual);

    if ($expected !== $actual) {
        throw new InvalidArgumentException(t('validation.home_category_layout'));
    }

    $database = db();
    $database->beginTransaction();

    try {
        $statement = $database->prepare(
            'UPDATE categories
             SET home_placement = :placement,
                 home_sort_order = :home_sort_order,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $now = now_sql();

        if ($topCategoryId > 0) {
            $statement->execute([
                'placement' => 'top',
                'home_sort_order' => 0,
                'updated_at' => $now,
                'id' => $topCategoryId,
            ]);
        }

        foreach ($gridCategoryIds as $index => $categoryId) {
            $statement->execute([
                'placement' => 'grid',
                'home_sort_order' => ($index + 1) * 10,
                'updated_at' => $now,
                'id' => $categoryId,
            ]);
        }

        if ($bottomCategoryId > 0) {
            $statement->execute([
                'placement' => 'bottom',
                'home_sort_order' => 0,
                'updated_at' => $now,
                'id' => $bottomCategoryId,
            ]);
        }

        $database->commit();
    } catch (Throwable $error) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }

        throw $error;
    }

    reset_store_categories_cache();
}
