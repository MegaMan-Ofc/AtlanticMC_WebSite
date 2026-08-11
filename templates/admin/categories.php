<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.categories')) ?></h2>
        <p><?= e(t('admin.categories_text')) ?></p>
    </div>

    <span class="admin-result-count">
        <?= e(t('admin.results_count', ['count' => count($adminCategories)])) ?>
    </span>
</section>

<div class="admin-entity-grid admin-category-entity-grid">
    <button
        class="admin-entity-card admin-entity-card--create"
        type="button"
        data-dialog-open="admin-category-dialog-new"
        aria-haspopup="dialog"
        aria-controls="admin-category-dialog-new"
    >
        <span class="admin-entity-icon">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
        </span>
        <strong><?= e(t('admin.create_category')) ?></strong>
    </button>

    <?php foreach ($adminCategories as $category): ?>
        <button
            class="admin-entity-card admin-category-tile <?= (bool) $category['active'] ? '' : 'is-inactive' ?>"
            type="button"
            data-dialog-open="admin-category-dialog-<?= (int) $category['id'] ?>"
            aria-haspopup="dialog"
            aria-controls="admin-category-dialog-<?= (int) $category['id'] ?>"
            aria-label="<?= e(t('admin.edit_category', ['name' => (string) $category['name']])) ?>"
        >
            <span class="admin-product-tile-image">
                <?php if ((string) $category['image'] !== ''): ?>
                    <img src="<?= e(url((string) $category['image'])) ?>" alt="" loading="lazy">
                <?php else: ?>
                    <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                <?php endif; ?>
            </span>
            <strong><?= e((string) $category['name']) ?></strong>
            <small><?= e((string) $category['slug']) ?></small>
            <span class="admin-category-count">
                <?= e(t('admin.category_products_count', ['count' => (int) $category['product_count']])) ?>
            </span>
        </button>
    <?php endforeach; ?>
</div>

<?php if ($adminCategories === []): ?>
    <section class="admin-panel admin-empty-state">
        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
        <h3><?= e(t('admin.no_categories')) ?></h3>
        <p><?= e(t('admin.no_categories_text')) ?></p>
    </section>
<?php endif; ?>

<?php
$categoryForm = [
    'id' => 0,
    'slug' => '',
    'name' => '',
    'image' => '',
    'active' => 1,
    'sort_order' => count($adminCategories) * 10 + 10,
    'product_count' => 0,
];
require BASE_PATH . '/templates/admin/category-dialog.php';
?>

<?php foreach ($adminCategories as $categoryForm): ?>
    <?php require BASE_PATH . '/templates/admin/category-dialog.php'; ?>
<?php endforeach; ?>
