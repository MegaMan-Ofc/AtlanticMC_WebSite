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

    $sql = 'SELECT id, slug, name, image, active, sort_order, home_placement, home_sort_order, created_at, updated_at FROM categories';

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
