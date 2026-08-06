<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.products')) ?></h2>
        <p><?= e(t('admin.products_text')) ?></p>
    </div>

    <span
        id="admin-products-count"
        class="admin-result-count"
    >
        <?= e(t('admin.results_count', [
            'count' => count($adminProducts),
        ])) ?>
    </span>
</section>

<form
    class="admin-panel admin-filter-form"
    action="<?= e(route_url('admin')) ?>"
    method="get"
    autocomplete="off"
    data-admin-filter-form
    data-ajax-endpoint="<?= e(url('ajax/admin-filter.php')) ?>"
    data-results-target="admin-products-results"
    data-count-target="admin-products-count"
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


        <div class="admin-field">
            <label for="filter-product-sort">
                <?= e(t('admin.sort_by')) ?>
            </label>

            <select
                id="filter-product-sort"
                name="sort"
            >
                <?php foreach ([
                    '' => 'admin.sort_catalog',
                    'name_asc' => 'admin.sort_name_asc',
                    'name_desc' => 'admin.sort_name_desc',
                    'price_asc' => 'admin.sort_price_asc',
                    'price_desc' => 'admin.sort_price_desc',
                    'created_desc' => 'admin.sort_created_desc',
                    'created_asc' => 'admin.sort_created_asc',
                ] as $sortValue => $sortLabel): ?>
                    <option
                        value="<?= e($sortValue) ?>"
                        <?= $adminProductFilters['sort'] === $sortValue
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e(t($sortLabel)) ?>
                    </option>
                <?php endforeach; ?>
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
            data-admin-filter-clear
        >
            <?= e(t('admin.clear_filters')) ?>
        </a>
    </div>
</form>

<section
    id="admin-products-results"
    class="admin-results"
    data-admin-results
    aria-live="polite"
    aria-busy="false"
>
    <?php require BASE_PATH . '/templates/admin/products-results.php'; ?>
</section>
