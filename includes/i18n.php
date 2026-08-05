<?php

declare(strict_types=1);

const STORE_LANGUAGES = ['pt', 'en'];

function normalize_language(?string $language): ?string
{
    if ($language === null) {
        return null;
    }

    $language = strtolower(trim($language));
    $language = str_replace('_', '-', $language);
    $language = explode('-', $language, 2)[0];

    return in_array($language, STORE_LANGUAGES, true) ? $language : null;
}

function current_language(): string
{
    $sessionLanguage = normalize_language(
        is_string($_SESSION['language'] ?? null) ? $_SESSION['language'] : null
    );

    if ($sessionLanguage !== null) {
        return $sessionLanguage;
    }

    return normalize_language((string) config('app.default_language', 'pt')) ?? 'pt';
}

function set_language(string $language): void
{
    $normalized = normalize_language($language);

    if ($normalized === null) {
        throw new InvalidArgumentException(t('validation.invalid_language'));
    }

    $_SESSION['language'] = $normalized;
}

function alternate_language(): string
{
    return current_language() === 'pt' ? 'en' : 'pt';
}

function language_label(?string $language = null): string
{
    return ($language ?? current_language()) === 'pt' ? 'PT' : 'ENG';
}

function translation_value(string $key, ?string $language = null): mixed
{
    static $catalogues = [];

    $language = normalize_language($language) ?? current_language();

    if (!array_key_exists($language, $catalogues)) {
        $path = BASE_PATH . '/translations/' . $language . '.php';
        $catalogue = is_file($path) ? require $path : [];
        $catalogues[$language] = is_array($catalogue) ? $catalogue : [];
    }

    return $catalogues[$language][$key] ?? null;
}

function t(string $key, array $replacements = [], ?string $fallback = null): string
{
    $value = translation_value($key);

    if (!is_string($value)) {
        $fallbackValue = translation_value($key, 'en');
        $value = is_string($fallbackValue) ? $fallbackValue : ($fallback ?? $key);
    }

    foreach ($replacements as $name => $replacement) {
        $value = str_replace(':' . $name, (string) $replacement, $value);
    }

    return $value;
}

function t_list(string $key, array $fallback = []): array
{
    $value = translation_value($key);

    if (!is_array($value)) {
        $englishValue = translation_value($key, 'en');
        $value = is_array($englishValue) ? $englishValue : $fallback;
    }

    return array_values(array_filter($value, 'is_string'));
}

function localized_product(array $product): array
{
    $slug = (string) ($product['slug'] ?? '');

    if ($slug === '') {
        return $product;
    }

    $prefix = 'products.' . $slug . '.';
    $product['name'] = t($prefix . 'name', [], (string) ($product['name'] ?? ''));
    $product['description'] = t($prefix . 'description', [], (string) ($product['description'] ?? ''));

    $metadata = product_metadata($product);

    if (isset($metadata['badge']) && is_string($metadata['badge'])) {
        $metadata['badge'] = t($prefix . 'badge', [], $metadata['badge']);
    }

    if (isset($metadata['features']) && is_array($metadata['features'])) {
        $metadata['features'] = t_list($prefix . 'features', $metadata['features']);
    }

    $product['localized_metadata'] = $metadata;

    return $product;
}

function localized_product_metadata(array $product): array
{
    $localized = localized_product($product);
    $metadata = $localized['localized_metadata'] ?? product_metadata($localized);

    return is_array($metadata) ? $metadata : [];
}

function localized_platform(string $platform): string
{
    return t('platform.' . strtolower($platform), [], ucfirst($platform));
}

function localized_category(string $category): string
{
    return t('category.' . strtolower($category), [], ucfirst($category));
}

function localized_order_status(string $status): string
{
    return t('status.' . strtolower($status), [], str_replace('_', ' ', ucfirst($status)));
}
