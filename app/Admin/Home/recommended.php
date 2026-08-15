<?php

declare(strict_types=1);

function admin_recommended_slots(): array
{
    $slots = array_fill(1, RECOMMENDED_PRODUCT_SLOTS, null);

    foreach (recommended_products(true) as $product) {
        $slot = (int) $product['slot'];

        if ($slot >= 1 && $slot <= RECOMMENDED_PRODUCT_SLOTS) {
            $slots[$slot] = $product;
        }
    }

    return $slots;
}

function admin_recommended_product_options(): array
{
    $statement = db()->query(
        'SELECT p.*,
                c.slug AS category_slug,
                c.name AS category_name
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE p.active = 1
           AND c.active = 1
         ORDER BY c.sort_order ASC, c.id ASC, p.sort_order ASC, p.id ASC'
    );

    return $statement->fetchAll();
}

function validate_recommended_slot(int $slot): void
{
    if ($slot < 1 || $slot > RECOMMENDED_PRODUCT_SLOTS) {
        throw new InvalidArgumentException(t('validation.recommended_slot'));
    }
}

function save_recommended_product(int $slot, int $productId): void
{
    validate_recommended_slot($slot);
    $product = product_by_id($productId, false);

    if ($product === null) {
        throw new InvalidArgumentException(t('validation.recommended_product'));
    }

    $database = db();
    $database->beginTransaction();

    try {
        $statement = $database->prepare(
            'DELETE FROM recommended_products
             WHERE slot = :slot OR product_id = :product_id'
        );
        $statement->execute([
            'slot' => $slot,
            'product_id' => $productId,
        ]);

        $now = now_sql();
        $statement = $database->prepare(
            'INSERT INTO recommended_products (slot, product_id, created_at, updated_at)
             VALUES (:slot, :product_id, :created_at, :updated_at)'
        );
        $statement->execute([
            'slot' => $slot,
            'product_id' => $productId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $database->commit();
    } catch (Throwable $error) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }

        throw $error;
    }
}

function remove_recommended_product(int $slot): void
{
    validate_recommended_slot($slot);
    $statement = db()->prepare('DELETE FROM recommended_products WHERE slot = :slot');
    $statement->execute(['slot' => $slot]);
}

function reorder_recommended_products(array $orderedProductIds): void
{
    if (count($orderedProductIds) !== RECOMMENDED_PRODUCT_SLOTS) {
        throw new InvalidArgumentException(t('validation.recommended_order'));
    }

    $normalized = [];

    foreach ($orderedProductIds as $productId) {
        $id = max(0, (int) $productId);
        $normalized[] = $id;
    }

    $selected = array_values(array_filter($normalized, static fn (int $id): bool => $id > 0));

    if (count($selected) !== count(array_unique($selected))) {
        throw new InvalidArgumentException(t('validation.recommended_order'));
    }

    $current = recommended_product_ids();
    sort($current);
    $submitted = $selected;
    sort($submitted);

    if ($current !== $submitted) {
        throw new InvalidArgumentException(t('validation.recommended_order'));
    }

    $database = db();
    $database->beginTransaction();

    try {
        $database->exec('DELETE FROM recommended_products');
        $statement = $database->prepare(
            'INSERT INTO recommended_products (slot, product_id, created_at, updated_at)
             VALUES (:slot, :product_id, :created_at, :updated_at)'
        );
        $now = now_sql();

        foreach ($normalized as $index => $productId) {
            if ($productId < 1) {
                continue;
            }

            $statement->execute([
                'slot' => $index + 1,
                'product_id' => $productId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $database->commit();
    } catch (Throwable $error) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }

        throw $error;
    }
}
