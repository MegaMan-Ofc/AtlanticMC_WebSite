<?php

declare(strict_types=1);

const STORE_CATEGORIES = ['ranks', 'rubis', 'keys', 'battlepass', 'boosters'];

function product_metadata(array $product): array
{
    $metadata = json_decode((string) ($product['metadata'] ?? '{}'), true);

    return is_array($metadata) ? $metadata : [];
}

function products_by_category(string $category, bool $includeInactive = false): array
{
    if (!in_array($category, STORE_CATEGORIES, true)) {
        return [];
    }

    $sql = 'SELECT * FROM products WHERE category = :category';

    if (!$includeInactive) {
        $sql .= ' AND active = 1';
    }

    $sql .= ' ORDER BY sort_order ASC, id ASC';
    $statement = db()->prepare($sql);
    $statement->execute(['category' => $category]);

    return $statement->fetchAll();
}

function all_products_admin(): array
{
    return db()->query('SELECT * FROM products ORDER BY category ASC, sort_order ASC, id ASC')->fetchAll();
}

function product_by_id(int $productId, bool $includeInactive = false): ?array
{
    $sql = 'SELECT * FROM products WHERE id = :id';

    if (!$includeInactive) {
        $sql .= ' AND active = 1';
    }

    $statement = db()->prepare($sql);
    $statement->execute(['id' => $productId]);
    $product = $statement->fetch();

    return is_array($product) ? $product : null;
}

function products_by_ids(array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));

    if ($ids === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $statement = db()->prepare("SELECT * FROM products WHERE active = 1 AND id IN ($placeholders)");
    $statement->execute($ids);
    $products = [];

    foreach ($statement->fetchAll() as $product) {
        $products[(int) $product['id']] = $product;
    }

    return $products;
}

function category_configuration(string $category): array
{
    $configurations = [
        'ranks' => [
            'title' => 'Atlantic Anarchy - VIPs',
            'heading' => 'VIP Ranks',
            'description' => 'Choose a permanent rank and add it securely to your server-side cart.',
            'bodyClass' => 'page-ranks',
            'styles' => ['css/pages/catalog.css', 'css/pages/ranks.css'],
        ],
        'rubis' => [
            'title' => 'Atlantic Anarchy - Rubis',
            'heading' => 'Rubis',
            'description' => 'Rubis packages with prices validated on the server.',
            'bodyClass' => 'page-rubis',
            'styles' => ['css/pages/catalog.css'],
        ],
        'keys' => [
            'title' => 'Atlantic Anarchy - Keys',
            'heading' => 'Crate Keys',
            'description' => 'Choose a key package for the Atlantic server.',
            'bodyClass' => 'page-keys',
            'styles' => ['css/pages/catalog.css'],
        ],
        'battlepass' => [
            'title' => 'Atlantic Anarchy - Battle Pass',
            'heading' => 'Battle Pass',
            'description' => 'Unlock seasonal progression and premium rewards.',
            'bodyClass' => 'page-battlepass',
            'styles' => ['css/pages/catalog.css'],
        ],
        'boosters' => [
            'title' => 'Atlantic Anarchy - Hearts',
            'heading' => 'Extra Hearts',
            'description' => 'Add extra-life heart bundles to your account.',
            'bodyClass' => 'page-boosters',
            'styles' => ['css/pages/catalog.css'],
        ],
    ];

    if (!isset($configurations[$category])) {
        throw new InvalidArgumentException('Unknown catalogue category.');
    }

    return $configurations[$category];
}

function save_product_from_admin(array $input): int
{
    $id = max(0, (int) ($input['id'] ?? 0));
    $category = strtolower(trim((string) ($input['category'] ?? '')));
    $name = trim((string) ($input['name'] ?? ''));
    $slug = strtolower(trim((string) ($input['slug'] ?? '')));
    $description = trim((string) ($input['description'] ?? ''));
    $image = trim((string) ($input['image'] ?? ''));
    $priceCents = parse_money_to_cents((string) ($input['price'] ?? '0'), 'Product price');
    $sortOrder = (int) ($input['sort_order'] ?? 0);
    $active = isset($input['active']) ? 1 : 0;
    $tebexPackageId = trim((string) ($input['tebex_package_id'] ?? ''));

    if (!in_array($category, STORE_CATEGORIES, true)) {
        throw new InvalidArgumentException('Invalid product category.');
    }

    if ($name === '' || strlen($name) > 120) {
        throw new InvalidArgumentException('Product name is required and must be at most 120 characters.');
    }

    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        throw new InvalidArgumentException('Slug may only contain lowercase letters, numbers and hyphens.');
    }

    if ($priceCents > 1_000_000) {
        throw new InvalidArgumentException('Invalid product price.');
    }

    if ($image !== '' && (!str_starts_with($image, 'assets/') || str_contains($image, '..'))) {
        throw new InvalidArgumentException('Product image must be inside the assets folder.');
    }

    if (strlen($description) > 1000) {
        throw new InvalidArgumentException('Product description must be at most 1000 characters.');
    }

    if ($tebexPackageId !== '' && !preg_match('/^[A-Za-z0-9_-]{1,64}$/', $tebexPackageId)) {
        throw new InvalidArgumentException('Invalid Tebex package ID.');
    }

    if ($sortOrder < -10000 || $sortOrder > 10000) {
        throw new InvalidArgumentException('Invalid product sort order.');
    }

    $now = now_sql();
    $parameters = [
        'slug' => $slug,
        'category' => $category,
        'name' => $name,
        'description' => $description,
        'image' => $image,
        'price_cents' => $priceCents,
        'currency' => config('app.currency', 'EUR'),
        'active' => $active,
        'sort_order' => $sortOrder,
        'tebex_package_id' => $tebexPackageId === '' ? null : $tebexPackageId,
        'updated_at' => $now,
    ];

    if ($id > 0) {
        $parameters['id'] = $id;
        $statement = db()->prepare(
            'UPDATE products
             SET slug = :slug, category = :category, name = :name, description = :description,
                 image = :image, price_cents = :price_cents, currency = :currency, active = :active,
                 sort_order = :sort_order, tebex_package_id = :tebex_package_id, updated_at = :updated_at
             WHERE id = :id'
        );
        $statement->execute($parameters);

        return $id;
    }

    $parameters['metadata'] = '{}';
    $parameters['created_at'] = $now;
    $statement = db()->prepare(
        'INSERT INTO products
         (slug, category, name, description, image, price_cents, currency, active, sort_order, tebex_package_id, metadata, created_at, updated_at)
         VALUES
         (:slug, :category, :name, :description, :image, :price_cents, :currency, :active, :sort_order, :tebex_package_id, :metadata, :created_at, :updated_at)'
    );
    $statement->execute($parameters);

    return (int) db()->lastInsertId();
}
