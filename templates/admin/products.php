<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.products')) ?></h2>
        <p><?= e(t('admin.products_text')) ?></p>
    </div>
</section>

<details class="admin-panel admin-create-panel">
    <summary><?= e(t('admin.create_product')) ?></summary>
    <form class="admin-form" action="<?= e(url('actions/admin_save_product.php')) ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="0">
        <div class="admin-form-grid">
            <div class="admin-field"><label for="new-product-name"><?= e(t('common.name')) ?></label><input id="new-product-name" name="name" maxlength="120" required></div>
            <div class="admin-field"><label for="new-product-slug"><?= e(t('admin.slug')) ?></label><input id="new-product-slug" name="slug" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" required></div>
            <div class="admin-field"><label for="new-product-category"><?= e(t('common.category')) ?></label><select id="new-product-category" name="category"><?php foreach (STORE_CATEGORIES as $categoryName): ?><option value="<?= e($categoryName) ?>"><?= e(localized_category($categoryName)) ?></option><?php endforeach; ?></select></div>
            <div class="admin-field"><label for="new-product-price"><?= e(t('admin.price_eur')) ?></label><input id="new-product-price" name="price" inputmode="decimal" value="0.00" required></div>
            <div class="admin-field"><label for="new-product-image"><?= e(t('admin.image_path')) ?></label><input id="new-product-image" name="image" placeholder="assets/product.png"></div>
            <div class="admin-field"><label for="new-product-tebex"><?= e(t('admin.tebex_package_id')) ?></label><input id="new-product-tebex" name="tebex_package_id" maxlength="64"></div>
            <div class="admin-field admin-field--full"><label for="new-product-description"><?= e(t('common.description')) ?></label><textarea id="new-product-description" name="description" rows="3" maxlength="1000"></textarea></div>
            <div class="admin-field"><label for="new-product-sort"><?= e(t('admin.sort_order')) ?></label><input id="new-product-sort" name="sort_order" type="number" value="0"></div>
            <label class="admin-check"><input name="active" type="checkbox" value="1" checked><span><?= e(t('common.active')) ?></span></label>
        </div>
        <button class="button button--primary" type="submit"><?= e(t('admin.create_product')) ?></button>
    </form>
</details>

<div class="admin-product-list">
    <?php foreach ($adminProducts as $product): ?>
        <form class="admin-panel admin-product-card" action="<?= e(url('actions/admin_save_product.php')) ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
            <div class="admin-product-preview">
                <img src="<?= e(url((string) $product['image'])) ?>" alt="">
                <div><strong><?= e((string) $product['name']) ?></strong><span><?= e(localized_category((string) $product['category'])) ?></span></div>
            </div>
            <div class="admin-form-grid">
                <div class="admin-field"><label><?= e(t('common.name')) ?></label><input name="name" value="<?= e($product['name']) ?>" maxlength="120" required></div>
                <div class="admin-field"><label><?= e(t('admin.slug')) ?></label><input name="slug" value="<?= e($product['slug']) ?>" required></div>
                <div class="admin-field"><label><?= e(t('common.category')) ?></label><select name="category"><?php foreach (STORE_CATEGORIES as $categoryName): ?><option value="<?= e($categoryName) ?>" <?= $product['category'] === $categoryName ? 'selected' : '' ?>><?= e(localized_category($categoryName)) ?></option><?php endforeach; ?></select></div>
                <div class="admin-field"><label><?= e(t('admin.price_eur')) ?></label><input name="price" value="<?= e(number_format((int) $product['price_cents'] / 100, 2, '.', '')) ?>" inputmode="decimal" required></div>
                <div class="admin-field"><label><?= e(t('admin.image_path')) ?></label><input name="image" value="<?= e($product['image']) ?>"></div>
                <div class="admin-field"><label><?= e(t('admin.tebex_package_id')) ?></label><input name="tebex_package_id" value="<?= e($product['tebex_package_id'] ?? '') ?>" maxlength="64"></div>
                <div class="admin-field admin-field--full"><label><?= e(t('common.description')) ?></label><textarea name="description" rows="3" maxlength="1000"><?= e($product['description']) ?></textarea></div>
                <div class="admin-field"><label><?= e(t('admin.sort_order')) ?></label><input name="sort_order" type="number" value="<?= (int) $product['sort_order'] ?>"></div>
                <label class="admin-check"><input name="active" type="checkbox" value="1" <?= (bool) $product['active'] ? 'checked' : '' ?>><span><?= e(t('common.active')) ?></span></label>
            </div>
            <div class="admin-card-actions"><button class="button button--primary" type="submit"><?= e(t('common.save')) ?></button></div>
        </form>
    <?php endforeach; ?>
</div>
