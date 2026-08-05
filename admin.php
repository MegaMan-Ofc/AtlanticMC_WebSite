<?php require_once __DIR__ . '/controllers/admin.php'; ?>
<!DOCTYPE html>
<html lang="<?= e(current_language()) ?>">
<?php require __DIR__ . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div class="admin-shell">
    <header class="admin-header">
        <h1><?= e(t('admin.title')) ?></h1>
        <p><?= e(t('admin.subtitle')) ?></p>
        <a class="button button--ghost" href="<?= e(route_url('home')) ?>"><?= e(t('common.back_to_store')) ?></a>
    </header>
    <?php require __DIR__ . '/templates/flash.php'; ?>
    <?php if (!$adminConfigured): ?>
        <section class="config-section">
            <h2><?= e(t('admin.disabled')) ?></h2>
            <p><?= e(t('admin.disabled_text')) ?></p>
            <pre>php -r "echo password_hash('change-me', PASSWORD_DEFAULT), PHP_EOL;"</pre>
        </section>
    <?php elseif (!$adminAuthenticated): ?>
        <section class="config-section">
            <h2><?= e(t('admin.login_title')) ?></h2>
            <form class="config-form" action="<?= e(url('actions/admin_login.php')) ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-group"><label for="admin-username"><?= e(t('common.username')) ?></label><input id="admin-username" name="username" autocomplete="username" required></div>
                <div class="form-group"><label for="admin-password"><?= e(t('common.password')) ?></label><input id="admin-password" name="password" type="password" autocomplete="current-password" required></div>
                <button class="btn-save" type="submit"><?= e(t('common.login')) ?></button>
            </form>
        </section>
    <?php else: ?>
        <form action="<?= e(url('actions/admin_logout.php')) ?>" method="post" class="admin-logout-form"><?= csrf_field() ?><button class="button button--ghost" type="submit"><?= e(t('admin.logout')) ?></button></form>
        <section class="config-section">
            <div class="section-heading"><div><h2><?= e(t('admin.products')) ?></h2><p><?= e(t('admin.products_text')) ?></p></div></div>
            <form class="config-form admin-product-new" action="<?= e(url('actions/admin_save_product.php')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="0">
                <div class="form-row">
                    <div class="form-group"><label for="new-product-name"><?= e(t('common.name')) ?></label><input id="new-product-name" name="name" maxlength="120" required></div>
                    <div class="form-group"><label for="new-product-slug"><?= e(t('admin.slug')) ?></label><input id="new-product-slug" name="slug" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="new-product-category"><?= e(t('common.category')) ?></label>
                        <select id="new-product-category" name="category">
                            <?php foreach (STORE_CATEGORIES as $categoryName): ?>
                                <option value="<?= e($categoryName) ?>"><?= e(localized_category($categoryName)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label for="new-product-price"><?= e(t('admin.price_eur')) ?></label><input id="new-product-price" name="price" inputmode="decimal" value="0.00" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label for="new-product-image"><?= e(t('admin.image_path')) ?></label><input id="new-product-image" name="image" placeholder="assets/product.png"></div>
                    <div class="form-group"><label for="new-product-tebex"><?= e(t('admin.tebex_package_id')) ?></label><input id="new-product-tebex" name="tebex_package_id"></div>
                </div>
                <div class="form-group"><label for="new-product-description"><?= e(t('common.description')) ?></label><textarea id="new-product-description" name="description" rows="2"></textarea></div>
                <div class="form-row">
                    <div class="form-group"><label for="new-product-sort"><?= e(t('admin.sort_order')) ?></label><input id="new-product-sort" name="sort_order" type="number" value="0"></div>
                    <label class="admin-checkbox"><input name="active" type="checkbox" value="1" checked> <?= e(t('common.active')) ?></label>
                </div>
                <button class="btn-add-new" type="submit"><?= e(t('admin.create_product')) ?></button>
            </form>
            <div class="item-list">
                <?php foreach ($adminProducts as $product): ?>
                    <form class="item-card admin-product-card" action="<?= e(url('actions/admin_save_product.php')) ?>" method="post">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                        <img src="<?= e(url($product['image'])) ?>" alt="">
                        <div class="admin-product-fields">
                            <div class="form-row"><div class="form-group"><label><?= e(t('common.name')) ?></label><input name="name" value="<?= e($product['name']) ?>" required></div><div class="form-group"><label><?= e(t('admin.slug')) ?></label><input name="slug" value="<?= e($product['slug']) ?>" required></div></div>
                            <div class="form-row"><div class="form-group"><label><?= e(t('common.category')) ?></label><select name="category"><?php foreach (STORE_CATEGORIES as $categoryName): ?><option value="<?= e($categoryName) ?>" <?= $product['category'] === $categoryName ? 'selected' : '' ?>><?= e(localized_category($categoryName)) ?></option><?php endforeach; ?></select></div><div class="form-group"><label><?= e(t('admin.price_eur')) ?></label><input name="price" value="<?= e(number_format((int) $product['price_cents'] / 100, 2, '.', '')) ?>" inputmode="decimal" required></div></div>
                            <div class="form-row"><div class="form-group"><label><?= e(t('admin.image_path')) ?></label><input name="image" value="<?= e($product['image']) ?>"></div><div class="form-group"><label><?= e(t('admin.tebex_package_id')) ?></label><input name="tebex_package_id" value="<?= e($product['tebex_package_id'] ?? '') ?>"></div></div>
                            <div class="form-group"><label><?= e(t('common.description')) ?></label><textarea name="description" rows="2"><?= e($product['description']) ?></textarea></div>
                            <div class="form-row"><div class="form-group"><label><?= e(t('admin.sort_order')) ?></label><input name="sort_order" type="number" value="<?= (int) $product['sort_order'] ?>"></div><label class="admin-checkbox"><input name="active" type="checkbox" value="1" <?= (bool) $product['active'] ? 'checked' : '' ?>> <?= e(t('common.active')) ?></label></div>
                        </div>
                        <div class="item-actions"><button class="btn-save" type="submit"><?= e(t('common.save')) ?></button></div>
                    </form>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="config-section admin-section-gap">
            <div class="section-heading"><div><h2><?= e(t('admin.coupons')) ?></h2><p><?= e(t('admin.coupons_text')) ?></p></div></div>
            <form class="config-form admin-coupon-new" action="<?= e(url('actions/admin_save_coupon.php')) ?>" method="post">
                <?= csrf_field() ?><input type="hidden" name="id" value="0">
                <div class="form-row"><div class="form-group"><label><?= e(t('common.code')) ?></label><input name="code" required></div><div class="form-group"><label><?= e(t('common.type')) ?></label><select name="discount_type"><option value="percentage"><?= e(t('admin.percentage')) ?></option><option value="fixed"><?= e(t('admin.fixed_eur')) ?></option></select></div></div>
                <div class="form-row"><div class="form-group"><label><?= e(t('common.value')) ?></label><input name="discount_value" inputmode="decimal" required></div><div class="form-group"><label><?= e(t('admin.minimum_subtotal')) ?></label><input name="min_subtotal" value="0" inputmode="decimal"></div></div>
                <div class="form-row"><div class="form-group"><label><?= e(t('admin.maximum_uses')) ?></label><input name="max_uses" type="number"></div><div class="form-group"><label><?= e(t('admin.expires_at')) ?></label><input name="expires_at" type="datetime-local"></div></div>
                <label class="admin-checkbox"><input name="active" type="checkbox" value="1" checked> <?= e(t('common.active')) ?></label><button class="btn-add-new" type="submit"><?= e(t('admin.create_coupon')) ?></button>
            </form>
            <div class="item-list">
                <?php foreach ($adminCoupons as $coupon): ?>
                    <form class="item-card admin-coupon-card" action="<?= e(url('actions/admin_save_coupon.php')) ?>" method="post">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $coupon['id'] ?>">
                        <div class="admin-product-fields">
                            <div class="form-row"><div class="form-group"><label><?= e(t('common.code')) ?></label><input name="code" value="<?= e($coupon['code']) ?>" required></div><div class="form-group"><label><?= e(t('common.type')) ?></label><select name="discount_type"><option value="percentage" <?= $coupon['discount_type'] === 'percentage' ? 'selected' : '' ?>><?= e(t('admin.percentage')) ?></option><option value="fixed" <?= $coupon['discount_type'] === 'fixed' ? 'selected' : '' ?>><?= e(t('admin.fixed_eur')) ?></option></select></div></div>
                            <div class="form-row"><div class="form-group"><label><?= e(t('common.value')) ?></label><input name="discount_value" value="<?= e($coupon['discount_type'] === 'fixed' ? number_format((int) $coupon['discount_value'] / 100, 2, '.', '') : $coupon['discount_value']) ?>"></div><div class="form-group"><label><?= e(t('admin.minimum_subtotal')) ?></label><input name="min_subtotal" value="<?= e(number_format((int) $coupon['min_subtotal_cents'] / 100, 2, '.', '')) ?>"></div></div>
                            <div class="form-row"><div class="form-group"><label><?= e(t('admin.maximum_uses')) ?></label><input name="max_uses" value="<?= e($coupon['max_uses'] ?? '') ?>" type="number"></div><div class="form-group"><label><?= e(t('admin.expires_at')) ?></label><input name="expires_at" value="<?= e($coupon['expires_at'] ? date('Y-m-d\\TH:i', strtotime($coupon['expires_at'])) : '') ?>" type="datetime-local"></div></div>
                            <label class="admin-checkbox"><input name="active" type="checkbox" value="1" <?= (bool) $coupon['active'] ? 'checked' : '' ?>> <?= e(t('admin.used_times', ['count' => (int) $coupon['used_count']])) ?></label>
                        </div>
                        <div class="item-actions"><button class="btn-save" type="submit"><?= e(t('common.save')) ?></button><button class="btn-delete" type="submit" formaction="<?= e(url('actions/admin_delete_coupon.php')) ?>"><?= e(t('common.delete')) ?></button></div>
                    </form>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="config-section admin-section-gap">
            <div class="section-heading"><div><h2><?= e(t('admin.recent_orders')) ?></h2><p><?= e(t('admin.orders_text')) ?></p></div></div>
            <?php if ($adminOrders === []): ?>
                <p><?= e(t('admin.no_orders')) ?></p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-orders-table">
                        <thead><tr><th><?= e(t('common.order')) ?></th><th><?= e(t('common.player')) ?></th><th><?= e(t('common.total')) ?></th><th><?= e(t('common.coupon')) ?></th><th><?= e(t('common.provider')) ?></th><th><?= e(t('common.status')) ?></th><th><?= e(t('common.created')) ?></th></tr></thead>
                        <tbody>
                        <?php foreach ($adminOrders as $adminOrder): ?>
                            <tr>
                                <td><code><?= e($adminOrder['public_token']) ?></code></td>
                                <td><?= e($adminOrder['minecraft_name']) ?> <small>(<?= e(localized_platform((string) $adminOrder['minecraft_platform'])) ?>)</small></td>
                                <td><?= e(format_money((int) $adminOrder['total_cents'], $adminOrder['currency'])) ?></td>
                                <td><?= e($adminOrder['coupon_code'] ?? '—') ?></td>
                                <td><?= e($adminOrder['provider']) ?></td>
                                <td><span class="admin-status admin-status--<?= e($adminOrder['status']) ?>"><?= e(localized_order_status((string) $adminOrder['status'])) ?></span></td>
                                <td><?= e($adminOrder['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>
</body>
</html>
