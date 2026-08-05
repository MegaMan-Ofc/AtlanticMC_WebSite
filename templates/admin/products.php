<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.products')) ?></h2>
        <p><?= e(t('admin.products_text')) ?></p>
    </div>
</section>

<?php
$newProduct = [
    'id' => 0,
    'name' => '',
    'slug' => '',
    'category' => STORE_CATEGORIES[0],
    'price_cents' => 0,
    'image' => '',
    'tebex_package_id' => null,
    'description' => '',
    'sort_order' => 0,
    'active' => 1,
];
?>

<div class="admin-entity-grid" aria-label="<?= e(t('admin.products')) ?>">
    <button class="admin-entity-card admin-entity-card--create" type="button" data-dialog-open="admin-product-dialog-new" aria-haspopup="dialog" aria-controls="admin-product-dialog-new">
        <span class="admin-entity-icon"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
        <strong><?= e(t('admin.create_product')) ?></strong>
    </button>

    <?php foreach ($adminProducts as $product): ?>
        <button class="admin-entity-card admin-product-tile <?= (bool) $product['active'] ? '' : 'is-inactive' ?>" type="button" data-dialog-open="admin-product-dialog-<?= (int) $product['id'] ?>" aria-haspopup="dialog" aria-controls="admin-product-dialog-<?= (int) $product['id'] ?>" aria-label="<?= e(t('admin.edit_product', ['name' => (string) $product['name']])) ?>">
            <span class="admin-product-tile-image">
                <?php if ((string) $product['image'] !== ''): ?>
                    <img src="<?= e(url((string) $product['image'])) ?>" alt="" loading="lazy">
                <?php else: ?>
                    <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                <?php endif; ?>
            </span>
            <strong><?= e((string) $product['name']) ?></strong>
        </button>
    <?php endforeach; ?>
</div>

<?php $productForm = $newProduct; require BASE_PATH . '/templates/admin/product-dialog.php'; ?>
<?php foreach ($adminProducts as $productForm): ?>
    <?php require BASE_PATH . '/templates/admin/product-dialog.php'; ?>
<?php endforeach; ?>
