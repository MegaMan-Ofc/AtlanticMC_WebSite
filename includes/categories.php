<?php

declare(strict_types=1);

const STORE_CATEGORIES = ['ranks', 'rubis', 'keys', 'boosters'];
const EDITABLE_STORE_CATEGORIES = ['ranks', 'rubis', 'keys'];

function editable_category_defaults(): array
{
    return [
        'ranks' => [
            'image' => 'assets/diamante.png',
            'theme' => 'vips',
            'route' => 'ranks',
        ],
        'rubis' => [
            'image' => 'assets/rubis-saco-pequeno.png.png',
            'theme' => 'rubis',
            'route' => 'rubis',
        ],
        'keys' => [
            'image' => 'assets/atlantic-key.png',
            'theme' => 'keys',
            'route' => 'keys',
        ],
    ];
}

function is_editable_store_category(string $category): bool
{
    return in_array($category, EDITABLE_STORE_CATEGORIES, true);
}

function category_meta_key(string $category, string $field): string
{
    return 'store_category.' . $category . '.' . $field;
}

function reset_category_settings_cache(): void
{
    unset($GLOBALS['store_category_settings_cache']);
}

function saved_category_settings(): array
{
    if (isset($GLOBALS['store_category_settings_cache']) && is_array($GLOBALS['store_category_settings_cache'])) {
        return $GLOBALS['store_category_settings_cache'];
    }

    $keys = [];

    foreach (EDITABLE_STORE_CATEGORIES as $category) {
        $keys[] = category_meta_key($category, 'name');
        $keys[] = category_meta_key($category, 'image');
    }

    $placeholders = implode(',', array_fill(0, count($keys), '?'));
    $statement = db()->prepare(
        "SELECT meta_key, meta_value
         FROM app_meta
         WHERE meta_key IN ($placeholders)"
    );
    $statement->execute($keys);
    $settings = [];

    foreach ($statement->fetchAll() as $row) {
        $settings[(string) $row['meta_key']] = (string) $row['meta_value'];
    }

    $GLOBALS['store_category_settings_cache'] = $settings;

    return $settings;
}

function store_category_name(string $category): string
{
    if (!is_editable_store_category($category)) {
        return t('category.' . strtolower($category), [], ucfirst($category));
    }

    $settings = saved_category_settings();
    $savedName = trim((string) ($settings[category_meta_key($category, 'name')] ?? ''));

    return $savedName !== ''
        ? $savedName
        : t('category.' . $category, [], ucfirst($category));
}

function store_category_image(string $category): string
{
    $defaults = editable_category_defaults();

    if (!isset($defaults[$category])) {
        return '';
    }

    $settings = saved_category_settings();
    $savedImage = trim((string) ($settings[category_meta_key($category, 'image')] ?? ''));

    return $savedImage !== '' ? $savedImage : $defaults[$category]['image'];
}

function store_category_settings(string $category): array
{
    $defaults = editable_category_defaults();

    if (!isset($defaults[$category])) {
        throw new InvalidArgumentException(t('validation.category_not_editable'));
    }

    return [
        'key' => $category,
        'name' => store_category_name($category),
        'image' => store_category_image($category),
        'theme' => $defaults[$category]['theme'],
        'route' => $defaults[$category]['route'],
    ];
}

function editable_store_category_settings(): array
{
    return array_map(
        static fn (string $category): array => store_category_settings($category),
        EDITABLE_STORE_CATEGORIES
    );
}
