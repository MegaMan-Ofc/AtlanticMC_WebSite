<?php

declare(strict_types=1);

function admin_product_filters(): array
{
    $search = substr(trim(query_string('search')), 0, 120);
    $category = query_string('category');
    $state = query_string('state');

    if ($category !== '' && !in_array($category, STORE_CATEGORIES, true)) {
        $category = '';
    }

    if (!in_array($state, ['', 'active', 'inactive'], true)) {
        $state = '';
    }

    return [
        'search' => $search,
        'category' => $category,
        'state' => $state,
    ];
}

function admin_products_query(array $filters): array
{
    $conditions = [];
    $parameters = [];
    $search = (string) ($filters['search'] ?? '');
    $category = (string) ($filters['category'] ?? '');
    $state = (string) ($filters['state'] ?? '');

    if ($search !== '') {
        $searchPattern = '%' . $search . '%';

        $conditions[] = '(
            name LIKE :search_name
            OR slug LIKE :search_slug
            OR tebex_package_id LIKE :search_tebex
        )';

        $parameters['search_name'] = $searchPattern;
        $parameters['search_slug'] = $searchPattern;
        $parameters['search_tebex'] = $searchPattern;
    }

    if ($category !== '') {
        $conditions[] = 'category = :category';
        $parameters['category'] = $category;
    }

    if ($state !== '') {
        $conditions[] = 'active = :active';
        $parameters['active'] = $state === 'active' ? 1 : 0;
    }

    return [
        'where' => $conditions === []
            ? ''
            : ' WHERE ' . implode(' AND ', $conditions),
        'parameters' => $parameters,
    ];
}

function all_products_admin(array $filters = []): array
{
    $query = admin_products_query($filters);

    $statement = db()->prepare(
        'SELECT *
         FROM products'
        . $query['where']
        . ' ORDER BY category ASC, sort_order ASC, id ASC'
    );

    $statement->execute($query['parameters']);

    return $statement->fetchAll();
}

function save_product_from_admin(array $input): int
{
    $id = max(0, (int) ($input['id'] ?? 0));
    $category = strtolower(trim((string) ($input['category'] ?? '')));
    $name = trim((string) ($input['name'] ?? ''));
    $slug = strtolower(trim((string) ($input['slug'] ?? '')));
    $description = trim((string) ($input['description'] ?? ''));
    $image = trim((string) ($input['image'] ?? ''));
    $priceCents = parse_money_to_cents(
        (string) ($input['price'] ?? '0'),
        t('field.product_price')
    );
    $sortOrder = (int) ($input['sort_order'] ?? 0);
    $active = isset($input['active']) ? 1 : 0;
    $tebexPackageId = trim((string) ($input['tebex_package_id'] ?? ''));

    if (!in_array($category, STORE_CATEGORIES, true)) {
        throw new InvalidArgumentException(t('validation.product_category'));
    }

    if ($name === '' || strlen($name) > 120) {
        throw new InvalidArgumentException(t('validation.product_name'));
    }

    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        throw new InvalidArgumentException(t('validation.product_slug'));
    }

    if ($priceCents > 1_000_000) {
        throw new InvalidArgumentException(t('validation.product_price'));
    }

    if (
        $image !== ''
        && (!str_starts_with($image, 'assets/') || str_contains($image, '..'))
    ) {
        throw new InvalidArgumentException(t('validation.product_image'));
    }

    if (strlen($description) > 1000) {
        throw new InvalidArgumentException(t('validation.product_description'));
    }

    if (
        $tebexPackageId !== ''
        && !preg_match('/^[A-Za-z0-9_-]{1,64}$/', $tebexPackageId)
    ) {
        throw new InvalidArgumentException(t('validation.tebex_package'));
    }

    if ($sortOrder < -10000 || $sortOrder > 10000) {
        throw new InvalidArgumentException(t('validation.product_sort'));
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
             SET slug = :slug,
                 category = :category,
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

        if ($statement->rowCount() === 0 && product_by_id($id, true) === null) {
            throw new InvalidArgumentException(t('validation.product_not_found'));
        }

        return $id;
    }

    $parameters['metadata'] = '{}';
    $parameters['created_at'] = $now;

    $statement = db()->prepare(
        'INSERT INTO products
         (
             slug,
             category,
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

    return (int) db()->lastInsertId();
}

function delete_product_from_admin(int $id): void
{
    if ($id < 1) {
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

        $statement->execute([
            'id' => $id,
        ]);

        if ((int) $statement->fetchColumn() > 0) {
            throw new InvalidArgumentException(t('validation.product_has_orders'));
        }

        $statement = $database->prepare(
            'DELETE FROM products
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
        ]);

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
}
