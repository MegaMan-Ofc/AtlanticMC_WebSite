<?php

declare(strict_types=1);

const LEGAL_PAGE_SLUGS = ['privacy', 'terms', 'purchase-policy', 'rules'];

function legal_replace_tokens(mixed $value, array $replacements): mixed
{
    if (is_string($value)) {
        foreach ($replacements as $name => $replacement) {
            $value = str_replace(':' . $name, (string) $replacement, $value);
        }

        return $value;
    }

    if (!is_array($value)) {
        return $value;
    }

    foreach ($value as $key => $item) {
        $value[$key] = legal_replace_tokens($item, $replacements);
    }

    return $value;
}

function legal_last_updated(): string
{
    $value = trim((string) config('legal.last_updated', ''));
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

    if ($date === false) {
        return $value !== '' ? $value : date('Y-m-d');
    }

    return current_language() === 'pt'
        ? $date->format('d/m/Y')
        : $date->format('F j, Y');
}

function legal_operator_details(): array
{
    $details = [
        ['label' => t('legal.operator_name'), 'value' => (string) config('legal.operator_name', config('app.name'))],
        ['label' => t('legal.operator_email'), 'value' => (string) config('legal.privacy_email', config('app.support_email')), 'email' => true],
        ['label' => t('legal.operator_country'), 'value' => (string) config('legal.country', '')],
        ['label' => t('legal.operator_address'), 'value' => (string) config('legal.operator_address', '')],
        ['label' => t('legal.operator_tax_id'), 'value' => (string) config('legal.operator_tax_id', '')],
    ];

    return array_values(array_filter(
        $details,
        static fn (array $detail): bool => trim((string) ($detail['value'] ?? '')) !== ''
    ));
}

function legal_configuration_complete(): bool
{
    foreach (['operator_name', 'operator_address', 'operator_tax_id', 'country', 'privacy_email', 'last_updated'] as $key) {
        if (trim((string) config('legal.' . $key, '')) === '') {
            return false;
        }
    }

    return true;
}

function legal_navigation(): array
{
    return [
        ['slug' => 'privacy', 'label' => t('footer.privacy'), 'url' => url('privacy.php')],
        ['slug' => 'terms', 'label' => t('footer.terms'), 'url' => url('terms.php')],
        ['slug' => 'purchase-policy', 'label' => t('footer.purchase_policy'), 'url' => url('purchase-policy.php')],
        ['slug' => 'rules', 'label' => t('footer.rules'), 'url' => url('rules.php')],
    ];
}

function legal_page_data(string $slug): array
{
    if (!in_array($slug, LEGAL_PAGE_SLUGS, true)) {
        throw new InvalidArgumentException('Unknown legal page.');
    }

    $key = 'legal.' . $slug;
    $sections = translation_value($key . '.sections');

    if (!is_array($sections)) {
        $sections = [];
    }

    $replacements = [
        'store' => (string) config('app.name'),
        'server' => (string) config('app.server_ip'),
        'support_email' => (string) config('app.support_email'),
        'privacy_email' => (string) config('legal.privacy_email', config('app.support_email')),
        'country' => (string) config('legal.country', ''),
        'currency' => (string) config('app.currency', 'EUR'),
    ];

    return [
        'slug' => $slug,
        'title' => t($key . '.title'),
        'pageTitle' => t($key . '.page_title'),
        'description' => t($key . '.description'),
        'intro' => t($key . '.intro'),
        'icon' => match ($slug) {
            'privacy' => 'fa-shield-halved',
            'terms' => 'fa-file-contract',
            'purchase-policy' => 'fa-receipt',
            'rules' => 'fa-scale-balanced',
        },
        'sections' => legal_replace_tokens($sections, $replacements),
        'lastUpdated' => legal_last_updated(),
        'operator' => legal_operator_details(),
        'navigation' => legal_navigation(),
        'needsReview' => !legal_configuration_complete(),
    ];
}
