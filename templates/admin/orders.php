<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.orders')) ?></h2>
        <p><?= e(t('admin.orders_text')) ?></p>
    </div>

    <span
        id="admin-orders-count"
        class="admin-result-count"
    >
        <?= e(t('admin.results_count', [
            'count' => (int) $adminOrdersPage['total'],
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
    data-results-target="admin-orders-results"
    data-count-target="admin-orders-count"
>
    <input
        type="hidden"
        name="section"
        value="orders"
    >

    <div class="admin-filter-grid">
        <div class="admin-field">
            <label for="filter-player">
                <?= e(t('common.player')) ?>
            </label>

            <input
                id="filter-player"
                name="player"
                value="<?= e($adminOrderFilters['player']) ?>"
                maxlength="32"
            >
        </div>

        <div class="admin-field">
            <label for="filter-status">
                <?= e(t('common.status')) ?>
            </label>

            <select
                id="filter-status"
                name="status"
            >
                <option value="">
                    <?= e(t('admin.all_statuses')) ?>
                </option>

                <?php foreach (ADMIN_ORDER_STATUSES as $status): ?>
                    <option
                        value="<?= e($status) ?>"
                        <?= $adminOrderFilters['status'] === $status
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e(localized_order_status($status)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="admin-field">
            <label for="filter-order">
                <?= e(t('common.order')) ?>
            </label>

            <input
                id="filter-order"
                name="order"
                value="<?= e($adminOrderFilters['order']) ?>"
                maxlength="64"
            >
        </div>

        <div class="admin-field">
            <label for="filter-from">
                <?= e(t('admin.date_from')) ?>
            </label>

            <input
                id="filter-from"
                name="date_from"
                value="<?= e($adminOrderFilters['date_from']) ?>"
                type="date"
            >
        </div>

        <div class="admin-field">
            <label for="filter-to">
                <?= e(t('admin.date_to')) ?>
            </label>

            <input
                id="filter-to"
                name="date_to"
                value="<?= e($adminOrderFilters['date_to']) ?>"
                type="date"
            >
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
            href="<?= e(admin_section_url('orders')) ?>"
            data-admin-filter-clear
        >
            <?= e(t('admin.clear_filters')) ?>
        </a>
    </div>
</form>

<section
    id="admin-orders-results"
    class="admin-results"
    data-admin-results
    aria-live="polite"
    aria-busy="false"
>
    <?php require BASE_PATH . '/templates/admin/orders-results.php'; ?>
</section>
