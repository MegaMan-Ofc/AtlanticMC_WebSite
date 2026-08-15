<?php

declare(strict_types=1);

function product_search_normalize(string $value): string
{
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    $value = strtr($value, [
        'Á' => 'a', 'À' => 'a', 'Â' => 'a', 'Ã' => 'a', 'Ä' => 'a', 'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'É' => 'e', 'È' => 'e', 'Ê' => 'e', 'Ë' => 'e', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'Í' => 'i', 'Ì' => 'i', 'Î' => 'i', 'Ï' => 'i', 'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'Ó' => 'o', 'Ò' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'Ú' => 'u', 'Ù' => 'u', 'Û' => 'u', 'Ü' => 'u', 'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'Ç' => 'c', 'ç' => 'c', 'Ñ' => 'n', 'ñ' => 'n',
    ]);

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;

    return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
}

function product_search_tokens(string $value): array
{
    $normalized = product_search_normalize($value);

    if ($normalized === '') {
        return [];
    }

    return array_values(array_unique(array_filter(explode(' ', $normalized), static fn (string $token): bool => $token !== '')));
}

function product_search_any_starts_with(array $tokens, string $prefix): bool
{
    foreach ($tokens as $token) {
        if (str_starts_with($token, $prefix)) {
            return true;
        }
    }

    return false;
}

function product_search_fuzzy_token_score(string $queryToken, array $candidateTokens): int
{
    if (strlen($queryToken) < 3 || $candidateTokens === []) {
        return 0;
    }

    $allowedDistance = strlen($queryToken) <= 5 ? 1 : 2;
    $bestDistance = PHP_INT_MAX;

    foreach ($candidateTokens as $candidateToken) {
        if (abs(strlen($candidateToken) - strlen($queryToken)) > $allowedDistance) {
            continue;
        }

        $distance = levenshtein($queryToken, $candidateToken);

        if ($distance < $bestDistance) {
            $bestDistance = $distance;
        }
    }

    if ($bestDistance > $allowedDistance) {
        return 0;
    }

    return max(4, 14 - ($bestDistance * 5));
}

function product_search_category_aliases(string $slug): string
{
    return match ($slug) {
        'ranks' => 'rank ranks vip vips cargo cargos',
        'rubis' => 'rubi rubis ruby currency moeda moedas',
        'keys' => 'key keys chave chaves crate crates caixa caixas',
        'boosters' => 'booster boosters heart hearts coracao coracoes vida',
        default => '',
    };
}

function product_search_score(array $product, string $query): int
{
    $query = product_search_normalize($query);

    if ($query === '') {
        return 0;
    }

    $localized = localized_product($product);
    $metadata = localized_product_metadata($product);
    $name = product_search_normalize((string) ($localized['name'] ?? ''));
    $description = product_search_normalize((string) ($localized['description'] ?? ''));
    $categorySlug = (string) ($product['category_slug'] ?? $product['category'] ?? '');
    $category = product_search_normalize(
        (string) ($product['category_name'] ?? $categorySlug)
        . ' ' . $categorySlug
        . ' ' . product_search_category_aliases($categorySlug)
    );
    $slug = product_search_normalize((string) ($product['slug'] ?? ''));
    $metadataText = product_search_normalize(implode(' ', array_filter([
        is_string($metadata['badge'] ?? null) ? $metadata['badge'] : '',
        is_string($metadata['amount'] ?? null) ? $metadata['amount'] : '',
        isset($metadata['features']) && is_array($metadata['features']) ? implode(' ', array_filter($metadata['features'], 'is_string')) : '',
    ])));

    $score = 0;

    if ($name === $query) {
        $score += 180;
    } elseif (str_starts_with($name, $query)) {
        $score += 120;
    } elseif (str_contains($name, $query)) {
        $score += 85;
    }

    if ($slug === $query) {
        $score += 80;
    } elseif (str_contains($slug, $query)) {
        $score += 35;
    }

    if ($category === $query) {
        $score += 60;
    } elseif (str_contains($category, $query)) {
        $score += 28;
    }

    if (str_contains($description, $query)) {
        $score += 32;
    }

    if ($metadataText !== '' && str_contains($metadataText, $query)) {
        $score += 20;
    }

    $queryTokens = product_search_tokens($query);
    $nameTokens = product_search_tokens($name);
    $categoryTokens = product_search_tokens($category);
    $descriptionTokens = product_search_tokens($description);
    $metadataTokens = product_search_tokens($metadataText);
    $matchedTokens = 0;

    foreach ($queryTokens as $token) {
        $tokenScore = 0;

        if (in_array($token, $nameTokens, true)) {
            $tokenScore = max($tokenScore, 48);
        } elseif (product_search_any_starts_with($nameTokens, $token)) {
            $tokenScore = max($tokenScore, 34);
        } elseif (str_contains($name, $token)) {
            $tokenScore = max($tokenScore, 24);
        }

        if (in_array($token, $categoryTokens, true)) {
            $tokenScore = max($tokenScore, 26);
        } elseif (str_contains($category, $token)) {
            $tokenScore = max($tokenScore, 17);
        }

        if (in_array($token, $descriptionTokens, true) || str_contains($description, $token)) {
            $tokenScore = max($tokenScore, 12);
        }

        if ($metadataText !== '' && (in_array($token, $metadataTokens, true) || str_contains($metadataText, $token))) {
            $tokenScore = max($tokenScore, 10);
        }

        if ($tokenScore === 0) {
            $tokenScore = product_search_fuzzy_token_score($token, array_merge($nameTokens, $categoryTokens));
        }

        if ($tokenScore > 0) {
            $matchedTokens++;
            $score += $tokenScore;
        }
    }

    if ($queryTokens !== [] && $matchedTokens === count($queryTokens)) {
        $score += 28;
    }

    return $score;
}

function product_search_candidates(): array
{
    $statement = db()->query(
        'SELECT p.*, c.slug AS category_slug, c.name AS category_name, c.sort_order AS category_sort_order
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE p.active = 1
           AND c.active = 1'
    );

    return $statement->fetchAll();
}

function product_search_sort_options(): array
{
    return ['relevance', 'price-asc', 'price-desc', 'name'];
}

function product_search_results(string $query = '', string $category = '', bool $discountOnly = false, string $sort = 'relevance'): array
{
    $query = trim($query);
    $category = strtolower(trim($category));
    $sort = in_array($sort, product_search_sort_options(), true) ? $sort : 'relevance';
    $results = [];

    foreach (product_search_candidates() as $product) {
        if ($category !== '' && (string) $product['category_slug'] !== $category) {
            continue;
        }

        if ($discountOnly && !product_has_discount($product)) {
            continue;
        }

        $score = product_search_score($product, $query);

        if ($query !== '' && $score <= 0) {
            continue;
        }

        $product['_search_score'] = $score;
        $results[] = $product;
    }

    usort($results, static function (array $left, array $right) use ($sort, $query): int {
        if ($sort === 'price-asc' || $sort === 'price-desc') {
            $comparison = product_effective_price_cents($left) <=> product_effective_price_cents($right);

            if ($sort === 'price-desc') {
                $comparison *= -1;
            }

            return $comparison !== 0 ? $comparison : (int) $left['id'] <=> (int) $right['id'];
        }

        if ($sort === 'name') {
            $leftName = product_search_normalize((string) localized_product($left)['name']);
            $rightName = product_search_normalize((string) localized_product($right)['name']);
            $comparison = $leftName <=> $rightName;

            return $comparison !== 0 ? $comparison : (int) $left['id'] <=> (int) $right['id'];
        }

        if ($query !== '') {
            $comparison = (int) $right['_search_score'] <=> (int) $left['_search_score'];

            if ($comparison !== 0) {
                return $comparison;
            }
        }

        $categoryComparison = (int) ($left['category_sort_order'] ?? 0) <=> (int) ($right['category_sort_order'] ?? 0);

        if ($categoryComparison !== 0) {
            return $categoryComparison;
        }

        $sortComparison = (int) ($left['sort_order'] ?? 0) <=> (int) ($right['sort_order'] ?? 0);

        return $sortComparison !== 0 ? $sortComparison : (int) $left['id'] <=> (int) $right['id'];
    });

    return $results;
}

function product_search_query_parameters(
    string $query,
    string $category,
    bool $discountOnly,
    string $sort,
    array $overrides = []
): array {
    $parameters = [
        'q' => $query,
        'category' => $category,
        'discount' => $discountOnly ? '1' : '',
        'sort' => $sort === 'relevance' ? '' : $sort,
    ];

    foreach ($overrides as $name => $value) {
        $parameters[$name] = $value;
    }

    return array_filter($parameters, static fn (mixed $value): bool => $value !== '' && $value !== null && $value !== false);
}
