<?php

declare(strict_types=1);

function admin_product_filters(): array
{
    $search = substr(trim(query_string('search')), 0, 120);
    $categoryId = max(0, (int) query_string('category_id'));
    $state = query_string('state');
    $sort = query_string('sort');

    if (!in_array($state, ['', 'active', 'inactive'], true)) {
        $state = '';
    }

    if (!in_array($sort, [
        '',
        'name_asc',
        'name_desc',
        'price_asc',
        'price_desc',
        'created_asc',
        'created_desc',
    ], true)) {
        $sort = '';
    }

    return [
        'search' => $search,
        'category_id' => $categoryId,
        'state' => $state,
        'sort' => $sort,
    ];
}

function admin_products_query(array $filters): array
{
    $conditions = [];
    $parameters = [];
    $search = (string) ($filters['search'] ?? '');
    $categoryId = max(0, (int) ($filters['category_id'] ?? 0));
    $state = (string) ($filters['state'] ?? '');

    if ($search !== '') {
        $searchPattern = '%' . $search . '%';
        $conditions[] = '(
            p.name LIKE :search_name
            OR p.slug LIKE :search_slug
            OR p.tebex_package_id LIKE :search_tebex
        )';
        $parameters['search_name'] = $searchPattern;
        $parameters['search_slug'] = $searchPattern;
        $parameters['search_tebex'] = $searchPattern;
    }

    if ($categoryId > 0) {
        $conditions[] = 'p.category_id = :category_id';
        $parameters['category_id'] = $categoryId;
    }

    if ($state !== '') {
        $conditions[] = 'p.active = :active';
        $parameters['active'] = $state === 'active' ? 1 : 0;
    }

    return [
        'where' => $conditions === []
            ? ''
            : ' WHERE ' . implode(' AND ', $conditions),
        'parameters' => $parameters,
    ];
}

function admin_product_query_parameters(array $filters): array
{
    $query = [
        'search' => (string) ($filters['search'] ?? ''),
        'state' => (string) ($filters['state'] ?? ''),
        'sort' => (string) ($filters['sort'] ?? ''),
    ];
    $categoryId = max(0, (int) ($filters['category_id'] ?? 0));

    if ($categoryId > 0) {
        $query['category_id'] = $categoryId;
    }

    return array_filter(
        $query,
        static fn (string|int $value): bool => $value !== '' && $value !== 0
    );
}

function admin_product_order_by(array $filters): string
{
    return match ((string) ($filters['sort'] ?? '')) {
        'name_asc' => 'p.name ASC, p.id ASC',
        'name_desc' => 'p.name DESC, p.id DESC',
        'price_asc' => 'p.price_cents ASC, p.id ASC',
        'price_desc' => 'p.price_cents DESC, p.id DESC',
        'created_asc' => 'p.created_at ASC, p.id ASC',
        'created_desc' => 'p.created_at DESC, p.id DESC',
        default => 'c.sort_order ASC, c.id ASC, p.sort_order ASC, p.id ASC',
    };
}

function all_products_admin(array $filters = []): array
{
    $query = admin_products_query($filters);
    $statement = db()->prepare(
        'SELECT p.*,
                c.slug AS category_slug,
                c.name AS category_name,
                c.active AS category_active
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id'
        . $query['where']
        . ' ORDER BY ' . admin_product_order_by($filters)
    );
    $statement->execute($query['parameters']);

    return $statement->fetchAll();
}

function validate_product_image_path(string $image): void
{
    if ($image === '') {
        return;
    }

    if (
        strlen($image) > 255
        || str_contains($image, '..')
        || str_contains($image, '://')
        || !(
            preg_match('#^assets/[^/]+\.png$#i', $image) === 1
            || preg_match('#^uploads/products/product-[a-f0-9]{32}\.png$#', $image) === 1
        )
    ) {
        throw new InvalidArgumentException(t('validation.product_image'));
    }
}

function save_product_from_admin(array $input, ?string $uploadedImage = null): int
{
    $id = max(0, (int) ($input['id'] ?? 0));
    $categoryId = max(0, (int) ($input['category_id'] ?? 0));
    $category = store_category_by_id($categoryId, true);
    $name = trim((string) ($input['name'] ?? ''));
    $slug = strtolower(trim((string) ($input['slug'] ?? '')));
    $description = trim((string) ($input['description'] ?? ''));
    $priceCents = parse_money_to_cents(
        (string) ($input['price'] ?? '0'),
        t('field.product_price')
    );
    $sortOrder = (int) ($input['sort_order'] ?? 0);
    $active = isset($input['active']) ? 1 : 0;
    $tebexPackageId = trim((string) ($input['tebex_package_id'] ?? ''));
    $existing = $id > 0 ? product_by_id($id, true) : null;

    if ($id > 0 && $existing === null) {
        throw new InvalidArgumentException(t('validation.product_not_found'));
    }

    if ($category === null) {
        throw new InvalidArgumentException(t('validation.product_category'));
    }

    if ($name === '' || strlen($name) > 120) {
        throw new InvalidArgumentException(t('validation.product_name'));
    }

    if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1) {
        throw new InvalidArgumentException(t('validation.product_slug'));
    }

    $duplicateStatement = db()->prepare(
        'SELECT id FROM products WHERE slug = :slug AND id <> :id'
    );
    $duplicateStatement->execute([
        'slug' => $slug,
        'id' => $id,
    ]);

    if ($duplicateStatement->fetchColumn() !== false) {
        throw new InvalidArgumentException(t('validation.product_slug_exists'));
    }

    if ($priceCents > 1_000_000) {
        throw new InvalidArgumentException(t('validation.product_price'));
    }

    $image = $uploadedImage ?? (string) ($existing['image'] ?? '');
    validate_product_image_path($image);

    if (strlen($description) > 1000) {
        throw new InvalidArgumentException(t('validation.product_description'));
    }

    if (
        $tebexPackageId !== ''
        && preg_match('/^[A-Za-z0-9_-]{1,64}$/', $tebexPackageId) !== 1
    ) {
        throw new InvalidArgumentException(t('validation.tebex_package'));
    }

    if ($sortOrder < -10000 || $sortOrder > 10000) {
        throw new InvalidArgumentException(t('validation.product_sort'));
    }

    $now = now_sql();
    $oldImage = (string) ($existing['image'] ?? '');
    $parameters = [
        'slug' => $slug,
        'category' => (string) $category['slug'],
        'category_id' => (int) $category['id'],
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
             SET slug = :slug,
                 category = :category,
                 category_id = :category_id,
                 name = :name,
                 description = :description,
                 image = :image,
                 price_cents = :price_cents,
                 currency = :currency,
                 active = :active,
                 sort_order = :sort_order,
                 tebex_package_id = :tebex_package_id,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $statement->execute($parameters);
    } else {
        $parameters['metadata'] = '{}';
        $parameters['created_at'] = $now;
        $statement = db()->prepare(
            'INSERT INTO products
             (
                 slug,
                 category,
                 category_id,
                 name,
                 description,
                 image,
                 price_cents,
                 currency,
                 active,
                 sort_order,
                 tebex_package_id,
                 metadata,
                 created_at,
                 updated_at
             )
             VALUES
             (
                 :slug,
                 :category,
                 :category_id,
                 :name,
                 :description,
                 :image,
                 :price_cents,
                 :currency,
                 :active,
                 :sort_order,
                 :tebex_package_id,
                 :metadata,
                 :created_at,
                 :updated_at
             )'
        );
        $statement->execute($parameters);
        $id = (int) db()->lastInsertId();
    }

    if ($oldImage !== '' && $oldImage !== $image) {
        cleanup_unreferenced_media($oldImage);
    }

    return $id;
}

function delete_product_from_admin(int $id): void
{
    if ($id < 1) {
        throw new InvalidArgumentException(t('validation.product_not_found'));
    }

    $product = product_by_id($id, true);

    if ($product === null) {
        throw new InvalidArgumentException(t('validation.product_not_found'));
    }

    $database = db();
    $database->beginTransaction();

    try {
        $statement = $database->prepare(
            'SELECT COUNT(*)
             FROM order_items
             WHERE product_id = :id'
        );
        $statement->execute(['id' => $id]);

        if ((int) $statement->fetchColumn() > 0) {
            throw new InvalidArgumentException(t('validation.product_has_orders'));
        }

        $statement = $database->prepare('DELETE FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);

        if ($statement->rowCount() === 0) {
            throw new InvalidArgumentException(t('validation.product_not_found'));
        }

        $database->commit();
    } catch (Throwable $error) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }

        throw $error;
    }

    cart_remove($id);
    cleanup_unreferenced_media((string) $product['image']);
}
