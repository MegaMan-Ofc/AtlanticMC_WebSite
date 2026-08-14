<?php

declare(strict_types=1);

const ADMIN_IMAGE_MAX_BYTES = 5 * 1024 * 1024;
const ADMIN_IMAGE_MAX_DIMENSION = 8192;
const ADMIN_IMAGE_MAX_PIXELS = 30_000_000;

function admin_upload_kind(string $kind): string
{
    if (!in_array($kind, ['categories', 'products'], true)) {
        throw new InvalidArgumentException(t('validation.image_upload_type'));
    }

    return $kind;
}

function validate_png_file(string $temporaryPath, int $size): void
{
    if ($size < 1 || $size > ADMIN_IMAGE_MAX_BYTES || !is_file($temporaryPath)) {
        throw new InvalidArgumentException(t('validation.image_upload_size'));
    }

    $handle = @fopen($temporaryPath, 'rb');
    $signature = is_resource($handle) ? fread($handle, 8) : false;

    if (is_resource($handle)) {
        fclose($handle);
    }

    $imageInfo = @getimagesize($temporaryPath);

    if (
        $signature !== "\x89PNG\r\n\x1a\n"
        || !is_array($imageInfo)
        || ($imageInfo[2] ?? null) !== IMAGETYPE_PNG
        || strtolower((string) ($imageInfo['mime'] ?? '')) !== 'image/png'
    ) {
        throw new InvalidArgumentException(t('validation.image_upload_png'));
    }

    $width = (int) ($imageInfo[0] ?? 0);
    $height = (int) ($imageInfo[1] ?? 0);

    if (
        $width < 1
        || $height < 1
        || $width > ADMIN_IMAGE_MAX_DIMENSION
        || $height > ADMIN_IMAGE_MAX_DIMENSION
        || $width * $height > ADMIN_IMAGE_MAX_PIXELS
    ) {
        throw new InvalidArgumentException(t('validation.image_upload_dimensions'));
    }
}

function save_uploaded_png(array $file, string $kind): ?string
{
    $kind = admin_upload_kind($kind);
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (in_array($error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        throw new InvalidArgumentException(t('validation.image_upload_size'));
    }

    if ($error !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException(t('validation.image_upload_failed'));
    }

    $temporaryPath = (string) ($file['tmp_name'] ?? '');
    if ($temporaryPath === '' || (PHP_SAPI !== 'cli' && !is_uploaded_file($temporaryPath))) {
        throw new InvalidArgumentException(t('validation.image_upload_failed'));
    }

    $size = filesize($temporaryPath);

    if ($size === false) {
        throw new InvalidArgumentException(t('validation.image_upload_failed'));
    }

    validate_png_file($temporaryPath, (int) $size);

    $relativeDirectory = 'uploads/' . $kind;
    $directory = BASE_PATH . '/public_html/' . $relativeDirectory;

    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException(t('validation.image_upload_storage'));
    }

    $prefix = $kind === 'categories' ? 'category' : 'product';
    $filename = $prefix . '-' . bin2hex(random_bytes(16)) . '.png';
    $relativePath = $relativeDirectory . '/' . $filename;
    $destination = $directory . '/' . $filename;

    $stored = PHP_SAPI === 'cli'
        ? copy($temporaryPath, $destination)
        : move_uploaded_file($temporaryPath, $destination);

    if (!$stored) {
        throw new RuntimeException(t('validation.image_upload_storage'));
    }

    @chmod($destination, 0644);

    return $relativePath;
}

function is_managed_upload_path(string $path): bool
{
    return preg_match(
        '#^uploads/(?:categories/category|products/product)-[a-f0-9]{32}\.png$#',
        $path
    ) === 1;
}

function delete_managed_upload_file(string $path): void
{
    if (!is_managed_upload_path($path)) {
        return;
    }

    $absolutePath = BASE_PATH . '/public_html/' . $path;

    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

function media_path_is_referenced(string $path): bool
{
    if ($path === '') {
        return false;
    }

    $categoryStatement = db()->prepare('SELECT COUNT(*) FROM categories WHERE image = :image');
    $categoryStatement->execute(['image' => $path]);

    if ((int) $categoryStatement->fetchColumn() > 0) {
        return true;
    }

    $productStatement = db()->prepare('SELECT COUNT(*) FROM products WHERE image = :image');
    $productStatement->execute(['image' => $path]);

    return (int) $productStatement->fetchColumn() > 0;
}

function cleanup_unreferenced_media(string $path): void
{
    if (!is_managed_upload_path($path)) {
        return;
    }

    try {
        if (!media_path_is_referenced($path)) {
            delete_managed_upload_file($path);
        }
    } catch (Throwable) {
        return;
    }
}
