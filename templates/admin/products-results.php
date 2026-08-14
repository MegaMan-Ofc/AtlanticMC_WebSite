<?php
$newProduct = [
    'id' => 0,
    'name' => '',
    'slug' => '',
    'category_id' => (int) ($adminCategoryOptions[0]['id'] ?? 0),
    'category' => (string) ($adminCategoryOptions[0]['slug'] ?? ''),
    'price_cents' => 0,
    'discount_price_cents' => null,
    'image' => '',
    'tebex_package_id' => null,
    'description' => '',
    'sort_order' => 0,
    'active' => 1,
];
?>

<div
    class="admin-entity-grid"
    aria-label="<?= e(t('admin.products')) ?>"
>
    <button
        class="admin-entity-card admin-entity-card--create"
        type="button"
        data-dialog-open="admin-product-dialog-new"
        aria-haspopup="dialog"
        aria-controls="admin-product-dialog-new"
    >
        <span class="admin-entity-icon">
            <i
                class="fa-solid fa-plus"
                aria-hidden="true"
            ></i>
        </span>

        <strong>
            <?= e(t('admin.create_product')) ?>
        </strong>
    </button>

    <?php foreach ($adminProducts as $product): ?>
        <button
            class="admin-entity-card admin-product-tile <?= (bool) $product['active']
                ? ''
                : 'is-inactive' ?>"
            type="button"
            data-dialog-open="admin-product-dialog-<?= (int) $product['id'] ?>"
            aria-haspopup="dialog"
            aria-controls="admin-product-dialog-<?= (int) $product['id'] ?>"
            aria-label="<?= e(t('admin.edit_product', [
                'name' => (string) $product['name'],
            ])) ?>"
        >
            <span class="admin-product-tile-image">
                <?php if ((string) $product['image'] !== ''): ?>
                    <img
                        src="<?= e(url((string) $product['image'])) ?>"
                        alt=""
                        loading="lazy"
                    >
                <?php else: ?>
                    <i
                        class="fa-solid fa-box-open"
                        aria-hidden="true"
                    ></i>
                <?php endif; ?>
            </span>

            <strong>
                <?= e((string) $product['name']) ?>
            </strong>
            <small class="admin-product-category">
                <?= e((string) $product['category_name']) ?>
            </small>
        </button>
    <?php endforeach; ?>
</div>

<?php if ($adminProducts === []): ?>
    <section class="admin-panel admin-empty-state">
        <i
            class="fa-solid fa-box-open"
            aria-hidden="true"
        ></i>

        <h3>
            <?= e(t('admin.no_products')) ?>
        </h3>

        <p>
            <?= e(t('admin.no_products_text')) ?>
        </p>
    </section>
<?php endif; ?>

<?php
$productForm = $newProduct;

require BASE_PATH . '/templates/admin/product-dialog.php';
?>

<?php foreach ($adminProducts as $productForm): ?>
    <?php require BASE_PATH . '/templates/admin/product-dialog.php'; ?>
<?php endforeach; ?>
