<?php

declare(strict_types=1);

function validate_category_image_path(string $image): void
{
    if (
        $image === ''
        || strlen($image) > 255
        || str_contains($image, '..')
        || str_contains($image, '://')
        || !(
            preg_match('#^assets/[^/]+\.png$#i', $image) === 1
            || preg_match('#^uploads/categories/category-[a-f0-9]{32}\.png$#', $image) === 1
        )
    ) {
        throw new InvalidArgumentException(t('validation.category_image'));
    }
}

function validate_category_slug(string $slug): void
{
    if (
        strlen($slug) > 80
        || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1
    ) {
        throw new InvalidArgumentException(t('validation.category_slug'));
    }

    if (category_slug_is_reserved($slug)) {
        throw new InvalidArgumentException(t('validation.category_slug_reserved'));
    }
}

function all_categories_admin(): array
{
    $statement = db()->query(
        'SELECT c.id,
                c.slug,
                c.name,
                c.image,
                c.active,
                c.sort_order,
                c.created_at,
                c.updated_at,
                COUNT(p.id) AS product_count
         FROM categories c
         LEFT JOIN products p ON p.category_id = c.id
         GROUP BY c.id, c.slug, c.name, c.image, c.active, c.sort_order, c.created_at, c.updated_at
         ORDER BY c.sort_order ASC, c.id ASC'
    );

    return $statement->fetchAll();
}

function save_category_from_admin(array $input, ?string $uploadedImage = null): int
{
    $id = max(0, (int) ($input['id'] ?? 0));
    $name = trim((string) ($input['name'] ?? ''));
    $slug = strtolower(trim((string) ($input['slug'] ?? '')));
    $sortOrder = (int) ($input['sort_order'] ?? 0);
    $active = isset($input['active']) ? 1 : 0;
    $existing = $id > 0 ? store_category_by_id($id, true) : null;

    if ($id > 0 && $existing === null) {
        throw new InvalidArgumentException(t('validation.category_not_found'));
    }

    if ($existing !== null) {
        $slug = (string) $existing['slug'];
    }

    if ($name === '' || strlen($name) > 80) {
        throw new InvalidArgumentException(t('validation.category_name'));
    }

    validate_category_slug($slug);

    if ($sortOrder < -10000 || $sortOrder > 10000) {
        throw new InvalidArgumentException(t('validation.category_sort'));
    }

    $image = $uploadedImage ?? (string) ($existing['image'] ?? '');
    validate_category_image_path($image);

    $duplicateStatement = db()->prepare(
        'SELECT id FROM categories WHERE slug = :slug AND id <> :id'
    );
    $duplicateStatement->execute([
        'slug' => $slug,
        'id' => $id,
    ]);

    if ($duplicateStatement->fetchColumn() !== false) {
        throw new InvalidArgumentException(t('validation.category_slug_exists'));
    }

    $database = db();
    $startedTransaction = !$database->inTransaction();
    $now = now_sql();
    $oldImage = (string) ($existing['image'] ?? '');

    try {
        if ($startedTransaction) {
            $database->beginTransaction();
        }

        if ($id > 0) {
            $statement = $database->prepare(
                'UPDATE categories
                 SET slug = :slug,
                     name = :name,
                     image = :image,
                     active = :active,
                     sort_order = :sort_order,
                     updated_at = :updated_at
                 WHERE id = :id'
            );
            $statement->execute([
                'slug' => $slug,
                'name' => $name,
                'image' => $image,
                'active' => $active,
                'sort_order' => $sortOrder,
                'updated_at' => $now,
                'id' => $id,
            ]);
        } else {
            $statement = $database->prepare(
                'INSERT INTO categories
                 (slug, name, image, active, sort_order, created_at, updated_at)
                 VALUES
                 (:slug, :name, :image, :active, :sort_order, :created_at, :updated_at)'
            );
            $statement->execute([
                'slug' => $slug,
                'name' => $name,
                'image' => $image,
                'active' => $active,
                'sort_order' => $sortOrder,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $id = (int) $database->lastInsertId();
        }

        if ($startedTransaction && $database->inTransaction()) {
            $database->commit();
        }
    } catch (Throwable $error) {
        if ($startedTransaction && $database->inTransaction()) {
            $database->rollBack();
        }

        throw $error;
    }

    reset_store_categories_cache();

    if ($oldImage !== '' && $oldImage !== $image) {
        cleanup_unreferenced_media($oldImage);
    }

    return $id;
}

function delete_category_from_admin(int $id): void
{
    $category = store_category_by_id($id, true);

    if ($category === null) {
        throw new InvalidArgumentException(t('validation.category_not_found'));
    }

    $database = db();
    $database->beginTransaction();

    try {
        $countStatement = $database->prepare(
            'SELECT COUNT(*) FROM products WHERE category_id = :category_id'
        );
        $countStatement->execute(['category_id' => $id]);

        if ((int) $countStatement->fetchColumn() > 0) {
            throw new InvalidArgumentException(t('validation.category_has_products'));
        }

        $deleteStatement = $database->prepare('DELETE FROM categories WHERE id = :id');
        $deleteStatement->execute(['id' => $id]);

        if ($deleteStatement->rowCount() === 0) {
            throw new InvalidArgumentException(t('validation.category_not_found'));
        }

        $database->commit();
    } catch (Throwable $error) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }

        throw $error;
    }

    reset_store_categories_cache();
    cleanup_unreferenced_media((string) $category['image']);
}
