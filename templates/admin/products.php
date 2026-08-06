<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.products')) ?></h2>
        <p><?= e(t('admin.products_text')) ?></p>
    </div>

    <span class="admin-result-count">
        <?= e(t('admin.results_count', [
            'count' => count($adminProducts),
        ])) ?>
    </span>
</section>

<form
    class="admin-panel admin-filter-form"
    action="<?= e(route_url('admin')) ?>"
    method="get"
>
    <input
        type="hidden"
        name="section"
        value="products"
    >

    <div class="admin-filter-grid admin-filter-grid--products">
        <div class="admin-field">
            <label for="filter-product-search">
                <?= e(t('admin.product_search')) ?>
            </label>

            <input
                id="filter-product-search"
                name="search"
                value="<?= e($adminProductFilters['search']) ?>"
                maxlength="120"
            >
        </div>

        <div class="admin-field">
            <label for="filter-product-category">
                <?= e(t('common.category')) ?>
            </label>

            <select
                id="filter-product-category"
                name="category"
            >
                <option value="">
                    <?= e(t('admin.all_categories')) ?>
                </option>

                <?php foreach (STORE_CATEGORIES as $category): ?>
                    <option
                        value="<?= e($category) ?>"
                        <?= $adminProductFilters['category'] === $category
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e(localized_category($category)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="admin-field">
            <label for="filter-product-state">
                <?= e(t('common.status')) ?>
            </label>

            <select
                id="filter-product-state"
                name="state"
            >
                <option value="">
                    <?= e(t('admin.all_product_states')) ?>
                </option>

                <option
                    value="active"
                    <?= $adminProductFilters['state'] === 'active'
                        ? 'selected'
                        : '' ?>
                >
                    <?= e(t('admin.active_products_only')) ?>
                </option>

                <option
                    value="inactive"
                    <?= $adminProductFilters['state'] === 'inactive'
                        ? 'selected'
                        : '' ?>
                >
                    <?= e(t('admin.inactive_products_only')) ?>
                </option>
            </select>
        </div>
    </div>

    <div class="admin-filter-actions">
        <button
            class="button button--primary"
            type="submit"
        >
            <?= e(t('admin.filter')) ?>
        </button>

        <a
            class="button button--ghost"
            href="<?= e(admin_section_url('products')) ?>"
        >
            <?= e(t('admin.clear_filters')) ?>
        </a>
    </div>
</form>

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
