<?php if ($adminOrdersPage['orders'] === []): ?>
    <section class="admin-panel admin-empty-state">
        <i
            class="fa-solid fa-receipt"
            aria-hidden="true"
        ></i>

        <h3><?= e(t('admin.no_orders')) ?></h3>
        <p><?= e(t('admin.no_orders_text')) ?></p>
    </section>
<?php else: ?>
    <div class="admin-order-list">
        <?php foreach ($adminOrdersPage['orders'] as $order): ?>
            <article class="admin-panel admin-order-card">
                <div class="admin-order-summary">
                    <div>
                        <span><?= e(t('common.order')) ?></span>
                        <code><?= e($order['public_token']) ?></code>
                    </div>

                    <div>
                        <span><?= e(t('common.player')) ?></span>
                        <strong><?= e($order['minecraft_name']) ?></strong>
                        <small>
                            <?= e(localized_platform(
                                (string) $order['minecraft_platform']
                            )) ?>
                        </small>
                    </div>

                    <div>
                        <span><?= e(t('common.total')) ?></span>
                        <strong>
                            <?= e(format_money(
                                (int) $order['total_cents'],
                                (string) $order['currency']
                            )) ?>
                        </strong>
                    </div>

                    <div>
                        <span><?= e(t('common.status')) ?></span>

                        <strong
                            class="admin-status admin-status--<?= e($order['status']) ?>"
                        >
                            <?= e(localized_order_status(
                                (string) $order['status']
                            )) ?>
                        </strong>
                    </div>

                    <div>
                        <span><?= e(t('common.created')) ?></span>

                        <strong>
                            <?= e(format_admin_datetime(
                                (string) $order['created_at']
                            )) ?>
                        </strong>
                    </div>
                </div>

                <details class="admin-order-details">
                    <summary>
                        <?= e(t('admin.view_order_details')) ?>
                    </summary>

                    <div class="admin-order-detail-grid">
                        <div>
                            <span><?= e(t('common.subtotal')) ?></span>

                            <strong>
                                <?= e(format_money(
                                    (int) $order['subtotal_cents'],
                                    (string) $order['currency']
                                )) ?>
                            </strong>
                        </div>

                        <div>
                            <span><?= e(t('common.discount')) ?></span>

                            <strong>
                                <?= e(format_money(
                                    (int) $order['discount_cents'],
                                    (string) $order['currency']
                                )) ?>
                            </strong>
                        </div>

                        <div>
                            <span><?= e(t('common.coupon')) ?></span>
                            <strong><?= e($order['coupon_code'] ?? '—') ?></strong>
                        </div>

                        <div>
                            <span><?= e(t('common.provider')) ?></span>
                            <strong><?= e($order['provider']) ?></strong>
                        </div>

                        <div>
                            <span><?= e(t('admin.provider_reference')) ?></span>
                            <strong><?= e($order['provider_reference'] ?? '—') ?></strong>
                        </div>

                        <div>
                            <span><?= e(t('admin.last_update')) ?></span>

                            <strong>
                                <?= e(format_admin_datetime(
                                    (string) $order['updated_at']
                                )) ?>
                            </strong>
                        </div>
                    </div>

                    <div class="admin-order-items">
                        <h4>
                            <?= e(t('admin.purchased_items')) ?>
                        </h4>

                        <?php foreach ($order['items'] as $item): ?>
                            <div>
                                <span>
                                    <?= e($item['product_name']) ?>
                                    ×
                                    <?= (int) $item['quantity'] ?>
                                </span>

                                <strong>
                                    <?= e(format_money(
                                        (int) $item['line_total_cents'],
                                        (string) $order['currency']
                                    )) ?>
                                </strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($adminOrdersPage['pages'] > 1): ?>
        <nav
            class="admin-pagination"
            aria-label="<?= e(t('admin.pagination')) ?>"
            data-admin-pagination
        >
            <?php if ($adminOrdersPage['page'] > 1): ?>
                <a
                    class="button button--ghost"
                    href="<?= e(admin_section_url(
                        'orders',
                        array_merge(
                            admin_order_query_parameters($adminOrderFilters),
                            [
                                'page' => $adminOrdersPage['page'] - 1,
                            ]
                        )
                    )) ?>"
                >
                    <?= e(t('admin.previous')) ?>
                </a>
            <?php endif; ?>

            <span>
                <?= e(t('admin.page_of', [
                    'page' => (int) $adminOrdersPage['page'],
                    'pages' => (int) $adminOrdersPage['pages'],
                ])) ?>
            </span>

            <?php if (
                $adminOrdersPage['page']
                < $adminOrdersPage['pages']
            ): ?>
                <a
                    class="button button--ghost"
                    href="<?= e(admin_section_url(
                        'orders',
                        array_merge(
                            admin_order_query_parameters($adminOrderFilters),
                            [
                                'page' => $adminOrdersPage['page'] + 1,
                            ]
                        )
                    )) ?>"
                >
                    <?= e(t('admin.next')) ?>
                </a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
