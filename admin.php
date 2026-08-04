<?php require_once __DIR__ . '/controllers/admin.php'; ?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/includes/head.php'; ?>
<body class="<?= e($bodyClass) ?>">
<div class="admin-shell">
    <header class="admin-header"><h1>Atlantic Store Admin</h1><p>Private server-side product, price and coupon management.</p><a class="button button--ghost" href="<?= e(url('index.php')) ?>">Back to store</a></header>
    <?php require __DIR__ . '/templates/flash.php'; ?>
    <?php if (!$adminConfigured): ?>
        <section class="config-section"><h2>Admin disabled</h2><p>Create a password hash and set ADMIN_PASSWORD_HASH in .env.</p><pre>php -r "echo password_hash('change-me', PASSWORD_DEFAULT), PHP_EOL;"</pre></section>
    <?php elseif (!$adminAuthenticated): ?>
        <section class="config-section">
            <h2>Administrator login</h2>
            <form class="config-form" action="<?= e(url('actions/admin_login.php')) ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-group"><label for="admin-username">Username</label><input id="admin-username" name="username" autocomplete="username" required></div>
                <div class="form-group"><label for="admin-password">Password</label><input id="admin-password" name="password" type="password" autocomplete="current-password" required></div>
                <button class="btn-save" type="submit">Login</button>
            </form>
        </section>
    <?php else: ?>
        <form action="<?= e(url('actions/admin_logout.php')) ?>" method="post" class="admin-logout-form"><?= csrf_field() ?><button class="button button--ghost" type="submit">Logout admin</button></form>
        <section class="config-section">
            <div class="section-heading"><div><h2>Products</h2><p>All displayed prices come from this database.</p></div></div>
            <form class="config-form admin-product-new" action="<?= e(url('actions/admin_save_product.php')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="0">
                <div class="form-row">
                    <div class="form-group">
                        <label for="new-product-name">Name</label>
                        <input id="new-product-name" name="name" maxlength="120" required>
                    </div>
                    <div class="form-group">
                        <label for="new-product-slug">Slug</label>
                        <input id="new-product-slug" name="slug" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="new-product-category">Category</label>
                        <select id="new-product-category" name="category">
                            <?php foreach (STORE_CATEGORIES as $categoryName): ?>
                                <option value="<?= e($categoryName) ?>"><?= e($categoryName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="new-product-price">Price (€)</label>
                        <input id="new-product-price" name="price" inputmode="decimal" value="0.00" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="new-product-image">Image path</label>
                        <input id="new-product-image" name="image" placeholder="assets/product.png">
                    </div>
                    <div class="form-group">
                        <label for="new-product-tebex">Tebex package ID</label>
                        <input id="new-product-tebex" name="tebex_package_id">
                    </div>
                </div>
                <div class="form-group">
                    <label for="new-product-description">Description</label>
                    <textarea id="new-product-description" name="description" rows="2"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="new-product-sort">Sort order</label>
                        <input id="new-product-sort" name="sort_order" type="number" value="0">
                    </div>
                    <label class="admin-checkbox"><input name="active" type="checkbox" value="1" checked> Active</label>
                </div>
                <button class="btn-add-new" type="submit">Create product</button>
            </form>
            <div class="item-list">
                <?php foreach ($adminProducts as $product): ?>
                    <form class="item-card admin-product-card" action="<?= e(url('actions/admin_save_product.php')) ?>" method="post">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                        <img src="<?= e(url($product['image'])) ?>" alt="">
                        <div class="admin-product-fields">
                            <div class="form-row"><div class="form-group"><label>Name</label><input name="name" value="<?= e($product['name']) ?>" required></div><div class="form-group"><label>Slug</label><input name="slug" value="<?= e($product['slug']) ?>" required></div></div>
                            <div class="form-row"><div class="form-group"><label>Category</label><select name="category"><?php foreach (STORE_CATEGORIES as $categoryName): ?><option value="<?= e($categoryName) ?>" <?= $product['category'] === $categoryName ? 'selected' : '' ?>><?= e($categoryName) ?></option><?php endforeach; ?></select></div><div class="form-group"><label>Price (€)</label><input name="price" value="<?= e(number_format((int) $product['price_cents'] / 100, 2, '.', '')) ?>" inputmode="decimal" required></div></div>
                            <div class="form-row"><div class="form-group"><label>Image path</label><input name="image" value="<?= e($product['image']) ?>"></div><div class="form-group"><label>Tebex package ID</label><input name="tebex_package_id" value="<?= e($product['tebex_package_id'] ?? '') ?>"></div></div>
                            <div class="form-group"><label>Description</label><textarea name="description" rows="2"><?= e($product['description']) ?></textarea></div>
                            <div class="form-row"><div class="form-group"><label>Sort order</label><input name="sort_order" type="number" value="<?= (int) $product['sort_order'] ?>"></div><label class="admin-checkbox"><input name="active" type="checkbox" value="1" <?= (bool) $product['active'] ? 'checked' : '' ?>> Active</label></div>
                        </div>
                        <div class="item-actions"><button class="btn-save" type="submit">Save</button></div>
                    </form>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="config-section admin-section-gap">
            <div class="section-heading"><div><h2>Coupons</h2><p>Create percentage or fixed-value server-side coupons.</p></div></div>
            <form class="config-form admin-coupon-new" action="<?= e(url('actions/admin_save_coupon.php')) ?>" method="post">
                <?= csrf_field() ?><input type="hidden" name="id" value="0">
                <div class="form-row"><div class="form-group"><label>Code</label><input name="code" required></div><div class="form-group"><label>Type</label><select name="discount_type"><option value="percentage">Percentage</option><option value="fixed">Fixed EUR</option></select></div></div>
                <div class="form-row"><div class="form-group"><label>Value</label><input name="discount_value" inputmode="decimal" required></div><div class="form-group"><label>Minimum subtotal (€)</label><input name="min_subtotal" value="0" inputmode="decimal"></div></div>
                <div class="form-row"><div class="form-group"><label>Maximum uses</label><input name="max_uses" type="number"></div><div class="form-group"><label>Expires at</label><input name="expires_at" type="datetime-local"></div></div>
                <label class="admin-checkbox"><input name="active" type="checkbox" value="1" checked> Active</label><button class="btn-add-new" type="submit">Create coupon</button>
            </form>
            <div class="item-list">
                <?php foreach ($adminCoupons as $coupon): ?>
                    <form class="item-card admin-coupon-card" action="<?= e(url('actions/admin_save_coupon.php')) ?>" method="post">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $coupon['id'] ?>">
                        <div class="admin-product-fields">
                            <div class="form-row"><div class="form-group"><label>Code</label><input name="code" value="<?= e($coupon['code']) ?>" required></div><div class="form-group"><label>Type</label><select name="discount_type"><option value="percentage" <?= $coupon['discount_type'] === 'percentage' ? 'selected' : '' ?>>Percentage</option><option value="fixed" <?= $coupon['discount_type'] === 'fixed' ? 'selected' : '' ?>>Fixed EUR</option></select></div></div>
                            <div class="form-row"><div class="form-group"><label>Value</label><input name="discount_value" value="<?= e($coupon['discount_type'] === 'fixed' ? number_format((int) $coupon['discount_value'] / 100, 2, '.', '') : $coupon['discount_value']) ?>"></div><div class="form-group"><label>Minimum subtotal (€)</label><input name="min_subtotal" value="<?= e(number_format((int) $coupon['min_subtotal_cents'] / 100, 2, '.', '')) ?>"></div></div>
                            <div class="form-row"><div class="form-group"><label>Maximum uses</label><input name="max_uses" value="<?= e($coupon['max_uses'] ?? '') ?>" type="number"></div><div class="form-group"><label>Expires at</label><input name="expires_at" value="<?= e($coupon['expires_at'] ? date('Y-m-d\\TH:i', strtotime($coupon['expires_at'])) : '') ?>" type="datetime-local"></div></div>
                            <label class="admin-checkbox"><input name="active" type="checkbox" value="1" <?= (bool) $coupon['active'] ? 'checked' : '' ?>> Active · used <?= (int) $coupon['used_count'] ?> times</label>
                        </div>
                        <div class="item-actions"><button class="btn-save" type="submit">Save</button><button class="btn-delete" type="submit" formaction="<?= e(url('actions/admin_delete_coupon.php')) ?>">Delete</button></div>
                    </form>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="config-section admin-section-gap">
            <div class="section-heading"><div><h2>Recent orders</h2><p>Payment state is updated only by verified server-side callbacks.</p></div></div>
            <?php if ($adminOrders === []): ?>
                <p>No orders have been created yet.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-orders-table">
                        <thead><tr><th>Order</th><th>Player</th><th>Total</th><th>Coupon</th><th>Provider</th><th>Status</th><th>Created</th></tr></thead>
                        <tbody>
                        <?php foreach ($adminOrders as $adminOrder): ?>
                            <tr>
                                <td><code><?= e($adminOrder['public_token']) ?></code></td>
                                <td><?= e($adminOrder['minecraft_name']) ?></td>
                                <td><?= e(format_money((int) $adminOrder['total_cents'], $adminOrder['currency'])) ?></td>
                                <td><?= e($adminOrder['coupon_code'] ?? '—') ?></td>
                                <td><?= e($adminOrder['provider']) ?></td>
                                <td><span class="admin-status admin-status--<?= e($adminOrder['status']) ?>"><?= e(str_replace('_', ' ', $adminOrder['status'])) ?></span></td>
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
