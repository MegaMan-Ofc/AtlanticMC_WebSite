<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.coupons')) ?></h2>
        <p><?= e(t('admin.coupons_text')) ?></p>
    </div>

    <span
        id="admin-coupons-count"
        class="admin-result-count"
    >
        <?= e(t('admin.results_count', [
            'count' => count($adminCoupons),
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
    data-results-target="admin-coupons-results"
    data-count-target="admin-coupons-count"
>
    <input
        type="hidden"
        name="section"
        value="coupons"
    >

    <div class="admin-filter-grid admin-filter-grid--coupons">
        <div class="admin-field">
            <label for="filter-coupon-search">
                <?= e(t('admin.coupon_search')) ?>
            </label>

            <input
                id="filter-coupon-search"
                name="search"
                value="<?= e($adminCouponFilters['search']) ?>"
                maxlength="50"
            >
        </div>

        <div class="admin-field">
            <label for="filter-coupon-type">
                <?= e(t('common.type')) ?>
            </label>

            <select
                id="filter-coupon-type"
                name="type"
            >
                <option value="">
                    <?= e(t('admin.all_coupon_types')) ?>
                </option>
                <option
                    value="percentage"
                    <?= $adminCouponFilters['type'] === 'percentage'
                        ? 'selected'
                        : '' ?>
                >
                    <?= e(t('admin.percentage')) ?>
                </option>
                <option
                    value="fixed"
                    <?= $adminCouponFilters['type'] === 'fixed'
                        ? 'selected'
                        : '' ?>
                >
                    <?= e(t('admin.fixed_eur')) ?>
                </option>
            </select>
        </div>

        <div class="admin-field">
            <label for="filter-coupon-state">
                <?= e(t('common.status')) ?>
            </label>

            <select
                id="filter-coupon-state"
                name="state"
            >
                <?php foreach ([
                    '' => 'admin.all_coupon_states',
                    'available' => 'admin.available_coupons_only',
                    'inactive' => 'admin.inactive_coupons_only',
                    'expired' => 'admin.expired_coupons_only',
                    'exhausted' => 'admin.exhausted_coupons_only',
                ] as $stateValue => $stateLabel): ?>
                    <option
                        value="<?= e($stateValue) ?>"
                        <?= $adminCouponFilters['state'] === $stateValue
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e(t($stateLabel)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="admin-field">
            <label for="filter-coupon-sort">
                <?= e(t('admin.sort_by')) ?>
            </label>

            <select
                id="filter-coupon-sort"
                name="sort"
            >
                <?php foreach ([
                    '' => 'admin.sort_created_desc',
                    'created_asc' => 'admin.sort_created_asc',
                    'code_asc' => 'admin.sort_code_asc',
                    'code_desc' => 'admin.sort_code_desc',
                    'usage_desc' => 'admin.sort_usage_desc',
                    'usage_asc' => 'admin.sort_usage_asc',
                ] as $sortValue => $sortLabel): ?>
                    <option
                        value="<?= e($sortValue) ?>"
                        <?= $adminCouponFilters['sort'] === $sortValue
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
            href="<?= e(admin_section_url('coupons')) ?>"
            data-admin-filter-clear
        >
            <?= e(t('admin.clear_filters')) ?>
        </a>
    </div>
</form>

<section
    id="admin-coupons-results"
    class="admin-results"
    data-admin-results
    aria-live="polite"
    aria-busy="false"
>
    <?php require TEMPLATE_PATH . '/admin/commerce/coupons/results.php'; ?>
</section>
