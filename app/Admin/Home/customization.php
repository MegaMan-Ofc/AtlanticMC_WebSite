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

function admin_home_banner_text(string $value, int $maxLength, string $fieldLabel): string
{
    $value = trim($value);

    $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);

    if ($value !== '' && $length > $maxLength) {
        throw new InvalidArgumentException(t('validation.home_banner_text_length', [
            'field' => $fieldLabel,
            'max' => $maxLength,
        ]));
    }

    return $value;
}

function save_admin_home_banner_customization(int $categoryId, array $input): array
{
    $category = store_category_by_id($categoryId, true);

    if ($category === null) {
        throw new InvalidArgumentException(t('validation.category_not_found'));
    }

    $style = (string) ($input['style'] ?? 'auto');
    $imageSide = (string) ($input['image_side'] ?? 'right');
    $imageSize = (string) ($input['image_size'] ?? 'normal');

    if (!in_array($style, home_banner_style_options(), true)) {
        throw new InvalidArgumentException(t('validation.home_banner_style'));
    }

    if (!in_array($imageSide, home_banner_image_side_options(), true)) {
        throw new InvalidArgumentException(t('validation.home_banner_image_side'));
    }

    if (!in_array($imageSize, home_banner_image_size_options(), true)) {
        throw new InvalidArgumentException(t('validation.home_banner_image_size'));
    }

    $settings = [
        'kicker' => admin_home_banner_text((string) ($input['kicker'] ?? ''), 80, t('admin.home_banner_kicker_label')),
        'title' => admin_home_banner_text((string) ($input['title'] ?? ''), 120, t('admin.home_banner_title_label')),
        'text' => admin_home_banner_text((string) ($input['text'] ?? ''), 255, t('admin.home_banner_text_label')),
        'cta' => admin_home_banner_text((string) ($input['cta'] ?? ''), 80, t('admin.home_banner_cta_label')),
        'style' => $style,
        'image_side' => $imageSide,
        'image_size' => $imageSize,
        'show_watermark' => (bool) ($input['show_watermark'] ?? false),
        'show_cta' => (bool) ($input['show_cta'] ?? false),
    ];

    $database = db();
    $database->beginTransaction();

    try {
        $statement = $database->prepare(
            'UPDATE categories
             SET home_banner_kicker = :home_banner_kicker,
                 home_banner_title = :home_banner_title,
                 home_banner_text = :home_banner_text,
                 home_banner_cta = :home_banner_cta,
                 home_banner_style = :home_banner_style,
                 home_banner_image_side = :home_banner_image_side,
                 home_banner_image_size = :home_banner_image_size,
                 home_banner_show_watermark = :home_banner_show_watermark,
                 home_banner_show_cta = :home_banner_show_cta,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $statement->execute([
            'home_banner_kicker' => $settings['kicker'] !== '' ? $settings['kicker'] : null,
            'home_banner_title' => $settings['title'] !== '' ? $settings['title'] : null,
            'home_banner_text' => $settings['text'] !== '' ? $settings['text'] : null,
            'home_banner_cta' => $settings['cta'] !== '' ? $settings['cta'] : null,
            'home_banner_style' => $settings['style'],
            'home_banner_image_side' => $settings['image_side'],
            'home_banner_image_size' => $settings['image_size'],
            'home_banner_show_watermark' => $settings['show_watermark'] ? 1 : 0,
            'home_banner_show_cta' => $settings['show_cta'] ? 1 : 0,
            'updated_at' => now_sql(),
            'id' => $categoryId,
        ]);

        $reload = $database->prepare(
            'SELECT id, slug, name, image, active, sort_order, home_placement, home_sort_order,
                    home_banner_kicker, home_banner_title, home_banner_text, home_banner_cta,
                    home_banner_style, home_banner_image_side, home_banner_image_size,
                    home_banner_show_watermark, home_banner_show_cta, created_at, updated_at
             FROM categories
             WHERE id = :id
             LIMIT 1'
        );
        $reload->execute(['id' => $categoryId]);
        $persistedCategory = $reload->fetch();

        if (!is_array($persistedCategory)) {
            throw new RuntimeException(t('validation.category_not_found'));
        }

        $database->commit();
    } catch (Throwable $error) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }

        throw $error;
    }

    reset_store_categories_cache();

    return home_banner_settings($persistedCategory);
}
