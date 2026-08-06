<?php

declare(strict_types=1);

function validate_category_image_path(string $image): void
{
    if (
        $image === ''
        || strlen($image) > 255
        || !str_starts_with($image, 'assets/')
        || str_contains($image, '..')
        || str_contains($image, '://')
        || !preg_match('/\.(?:gif|jpe?g|png|svg|webp)$/i', $image)
    ) {
        throw new InvalidArgumentException(t('validation.category_image'));
    }
}

function save_category_from_admin(array $input): void
{
    $category = strtolower(trim((string) ($input['category'] ?? '')));
    $name = trim((string) ($input['name'] ?? ''));
    $image = trim((string) ($input['image'] ?? ''));

    if (!is_editable_store_category($category)) {
        throw new InvalidArgumentException(t('validation.category_not_editable'));
    }

    if ($name === '' || strlen($name) > 80) {
        throw new InvalidArgumentException(t('validation.category_name'));
    }

    validate_category_image_path($image);

    $database = db();
    $startedTransaction = !$database->inTransaction();

    try {
        if ($startedTransaction) {
            $database->beginTransaction();
        }

        $select = $database->prepare(
            'SELECT 1 FROM app_meta WHERE meta_key = :meta_key'
        );
        $insert = $database->prepare(
            'INSERT INTO app_meta (meta_key, meta_value)
             VALUES (:meta_key, :meta_value)'
        );
        $update = $database->prepare(
            'UPDATE app_meta
             SET meta_value = :meta_value
             WHERE meta_key = :meta_key'
        );

        foreach (['name' => $name, 'image' => $image] as $field => $value) {
            $key = category_meta_key($category, $field);
            $parameters = [
                'meta_key' => $key,
                'meta_value' => $value,
            ];

            $select->execute(['meta_key' => $key]);

            if ($select->fetchColumn() === false) {
                $insert->execute($parameters);
            } else {
                $update->execute($parameters);
            }
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

    reset_category_settings_cache();
}
