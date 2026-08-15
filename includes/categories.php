<?php

declare(strict_types=1);

function reset_store_categories_cache(): void
{
    unset($GLOBALS['store_categories_cache']);
}

function all_store_categories(bool $includeInactive = false): array
{
    $cacheKey = $includeInactive ? 'all' : 'active';

    if (isset($GLOBALS['store_categories_cache'][$cacheKey])) {
        return $GLOBALS['store_categories_cache'][$cacheKey];
    }

    $sql = 'SELECT id, slug, name, image, active, sort_order, home_placement, home_sort_order,
               home_banner_kicker, home_banner_title, home_banner_text, home_banner_cta,
               home_banner_style, home_banner_image_side, home_banner_image_size,
               home_banner_show_watermark, home_banner_show_cta, created_at, updated_at
        FROM categories';

    if (!$includeInactive) {
        $sql .= ' WHERE active = 1';
    }

    $sql .= ' ORDER BY sort_order ASC, id ASC';
    $categories = db()->query($sql)->fetchAll();
    $GLOBALS['store_categories_cache'][$cacheKey] = $categories;

    return $categories;
}

function store_category_by_id(int $id, bool $includeInactive = true): ?array
{
    if ($id < 1) {
        return null;
    }

    foreach (all_store_categories(true) as $category) {
        if ((int) $category['id'] !== $id) {
            continue;
        }

        if (!$includeInactive && !(bool) $category['active']) {
            return null;
        }

        return $category;
    }

    return null;
}

function store_category_by_slug(string $slug, bool $includeInactive = true): ?array
{
    $slug = strtolower(trim($slug));

    if ($slug === '') {
        return null;
    }

    foreach (all_store_categories(true) as $category) {
        if ((string) $category['slug'] !== $slug) {
            continue;
        }

        if (!$includeInactive && !(bool) $category['active']) {
            return null;
        }

        return $category;
    }

    return null;
}

function store_category_exists(string $slug, bool $includeInactive = true): bool
{
    return store_category_by_slug($slug, $includeInactive) !== null;
}

function store_category_name(string $slug): string
{
    $category = store_category_by_slug($slug, true);

    return $category === null
        ? t('category.' . strtolower($slug), [], ucfirst($slug))
        : (string) $category['name'];
}

function store_category_image(string $slug): string
{
    $category = store_category_by_slug($slug, true);

    return $category === null ? '' : (string) $category['image'];
}

function category_card_theme(string $slug): string
{
    return match ($slug) {
        'ranks' => 'vips',
        'rubis' => 'rubis',
        'keys' => 'keys',
        'boosters' => 'boosters',
        default => 'default',
    };
}

function home_banner_style_options(): array
{
    return ['auto', 'atlantic', 'sunset', 'emerald', 'violet', 'crimson'];
}

function home_banner_image_side_options(): array
{
    return ['left', 'right'];
}

function home_banner_image_size_options(): array
{
    return ['compact', 'normal', 'large'];
}

function home_banner_settings(array $category): array
{
    $style = (string) ($category['home_banner_style'] ?? 'auto');
    $imageSide = (string) ($category['home_banner_image_side'] ?? 'right');
    $imageSize = (string) ($category['home_banner_image_size'] ?? 'normal');

    return [
        'kicker' => trim((string) ($category['home_banner_kicker'] ?? '')),
        'title' => trim((string) ($category['home_banner_title'] ?? '')),
        'text' => trim((string) ($category['home_banner_text'] ?? '')),
        'cta' => trim((string) ($category['home_banner_cta'] ?? '')),
        'style' => in_array($style, home_banner_style_options(), true) ? $style : 'auto',
        'image_side' => in_array($imageSide, home_banner_image_side_options(), true) ? $imageSide : 'right',
        'image_size' => in_array($imageSize, home_banner_image_size_options(), true) ? $imageSize : 'normal',
        'show_watermark' => (bool) ($category['home_banner_show_watermark'] ?? true),
        'show_cta' => (bool) ($category['home_banner_show_cta'] ?? true),
    ];
}

function home_banner_is_customized(array $category): bool
{
    $settings = home_banner_settings($category);

    return $settings['kicker'] !== ''
        || $settings['title'] !== ''
        || $settings['text'] !== ''
        || $settings['cta'] !== ''
        || $settings['style'] !== 'auto'
        || $settings['image_side'] !== 'right'
        || $settings['image_size'] !== 'normal'
        || $settings['show_watermark'] !== true
        || $settings['show_cta'] !== true;
}

function home_category_view_model(array $category): array
{
    return [
        'id' => (int) $category['id'],
        'key' => (string) $category['slug'],
        'slug' => (string) $category['slug'],
        'name' => (string) $category['name'],
        'image' => (string) $category['image'],
        'active' => (bool) $category['active'],
        'sort_order' => (int) $category['sort_order'],
        'home_placement' => (string) ($category['home_placement'] ?? 'grid'),
        'home_sort_order' => (int) ($category['home_sort_order'] ?? $category['sort_order']),
        'theme' => category_card_theme((string) $category['slug']),
        'url' => category_url((string) $category['slug']),
        'banner' => home_banner_settings($category),
    ];
}

function home_store_category_layout(): array
{
    $categories = all_store_categories(false);

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
        $viewModel = home_category_view_model($category);

        if ($placement === 'top' && $layout['top'] === null) {
            $layout['top'] = $viewModel;
            continue;
        }

        if ($placement === 'bottom' && $layout['bottom'] === null) {
            $layout['bottom'] = $viewModel;
            continue;
        }

        $layout['grid'][] = $viewModel;
    }

    return $layout;
}

function home_store_categories(): array
{
    return home_store_category_layout()['grid'];
}
