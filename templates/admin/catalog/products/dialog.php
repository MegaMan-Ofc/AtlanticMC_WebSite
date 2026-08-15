<?php

$productId = (int) $productForm['id'];
$productDialogId = $productId > 0
    ? 'admin-product-dialog-' . $productId
    : 'admin-product-dialog-new';
$productTitleId = $productDialogId . '-title';
$productIsNew = $productId === 0;
$productImage = (string) ($productForm['image'] ?? '');
$productCategoryId = (int) ($productForm['category_id'] ?? 0);
$productDiscountPriceCents = isset($productForm['discount_price_cents'])
    && $productForm['discount_price_cents'] !== null
    ? (int) $productForm['discount_price_cents']
    : null;
$productDiscountEnabled = $productDiscountPriceCents !== null
    && $productDiscountPriceCents < (int) $productForm['price_cents'];
$hasCategoryOptions = $adminCategoryOptions !== [];

?>

<dialog
    class="admin-dialog"
    id="<?= e($productDialogId) ?>"
    aria-labelledby="<?= e($productTitleId) ?>"
>
    <form
        class="admin-dialog-form"
        action="<?= e(url('actions/admin_save_product.php')) ?>"
        method="post"
        enctype="multipart/form-data"
        <?php if (!$productIsNew): ?>
            data-confirm-delete="<?= e(t('admin.delete_product_confirm')) ?>"
        <?php endif; ?>
    >
        <?= csrf_field() ?>

        <input type="hidden" name="id" value="<?= $productId ?>">

        <header class="admin-dialog-header">
            <div>
                <span class="admin-dialog-kicker">
                    <?= e(t('admin.section_products')) ?>
                </span>

                <h3 id="<?= e($productTitleId) ?>">
                    <?= e(
                        $productIsNew
                            ? t('admin.create_product')
                            : (string) $productForm['name']
                    ) ?>
                </h3>
            </div>

            <button
                class="admin-dialog-close"
                type="button"
                data-dialog-close
                aria-label="<?= e(t('common.close')) ?>"
            >
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </header>

        <div class="admin-dialog-body">
            <?php if (!$hasCategoryOptions): ?>
                <div class="admin-inline-note admin-inline-note--warning">
                    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                    <span><?= e(t('admin.product_requires_category')) ?></span>
                    <a href="<?= e(admin_section_url('categories')) ?>"><?= e(t('admin.section_categories')) ?></a>
                </div>
            <?php endif; ?>

            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="<?= e($productDialogId) ?>-name">
                        <?= e(t('common.name')) ?>
                    </label>
                    <input
                        id="<?= e($productDialogId) ?>-name"
                        name="name"
                        value="<?= e((string) $productForm['name']) ?>"
                        maxlength="120"
                        required
                        <?= $productIsNew ? 'data-admin-slug-source' : '' ?>
                    >
                </div>

                <div class="admin-field">
                    <label for="<?= e($productDialogId) ?>-slug">
                        <?= e(t('admin.slug')) ?>
                    </label>
                    <input
                        id="<?= e($productDialogId) ?>-slug"
                        name="slug"
                        value="<?= e((string) $productForm['slug']) ?>"
                        maxlength="100"
                        pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                        required
                        <?= $productIsNew ? 'data-admin-slug-target' : '' ?>
                    >
                </div>

                <div class="admin-field">
                    <label for="<?= e($productDialogId) ?>-category">
                        <?= e(t('common.category')) ?>
                    </label>
                    <select
                        id="<?= e($productDialogId) ?>-category"
                        name="category_id"
                        required
                        <?= !$hasCategoryOptions ? 'disabled' : '' ?>
                    >
                        <?php if (!$hasCategoryOptions): ?>
                            <option value=""><?= e(t('admin.no_categories_option')) ?></option>
                        <?php else: ?>
                            <?php foreach ($adminCategoryOptions as $categoryOption): ?>
                                <option
                                    value="<?= (int) $categoryOption['id'] ?>"
                                    <?= $productCategoryId === (int) $categoryOption['id'] ? 'selected' : '' ?>
                                >
                                    <?= e((string) $categoryOption['name']) ?><?= (bool) $categoryOption['active'] ? '' : ' · ' . e(t('common.inactive')) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="admin-field">
                    <label for="<?= e($productDialogId) ?>-price">
                        <?= e(t('admin.price_eur')) ?>
                    </label>
                    <input
                        id="<?= e($productDialogId) ?>-price"
                        name="price"
                        value="<?= e(number_format((int) $productForm['price_cents'] / 100, 2, '.', '')) ?>"
                        inputmode="decimal"
                        required
                    >
                </div>

                <div
                    class="admin-field admin-field--full admin-discount-control <?= $productDiscountEnabled ? 'is-active' : '' ?>"
                    data-admin-discount-control
                >
                    <input
                        type="hidden"
                        name="discount_enabled"
                        value="<?= $productDiscountEnabled ? '1' : '0' ?>"
                        data-admin-discount-enabled
                    >

                    <div class="admin-discount-heading">
                        <div>
                            <span class="admin-discount-title">
                                <?= e(t('admin.product_discount')) ?>
                            </span>
                            <small><?= e(t('admin.product_discount_help')) ?></small>
                        </div>

                        <button
                            class="button <?= $productDiscountEnabled ? 'button--primary' : 'button--ghost' ?>"
                            type="button"
                            aria-pressed="<?= $productDiscountEnabled ? 'true' : 'false' ?>"
                            data-admin-discount-toggle
                            data-enable-label="<?= e(t('admin.enable_discount')) ?>"
                            data-disable-label="<?= e(t('admin.disable_discount')) ?>"
                        >
                            <i class="fa-solid fa-percent" aria-hidden="true"></i>
                            <span data-admin-discount-toggle-label>
                                <?= e($productDiscountEnabled ? t('admin.disable_discount') : t('admin.enable_discount')) ?>
                            </span>
                        </button>
                    </div>

                    <div
                        class="admin-discount-fields"
                        data-admin-discount-fields
                        <?= $productDiscountEnabled ? '' : 'hidden' ?>
                    >
                        <label for="<?= e($productDialogId) ?>-discount-price">
                            <?= e(t('admin.discount_price_eur')) ?>
                        </label>
                        <input
                            id="<?= e($productDialogId) ?>-discount-price"
                            name="discount_price"
                            value="<?= $productDiscountPriceCents !== null
                                ? e(number_format($productDiscountPriceCents / 100, 2, '.', ''))
                                : '' ?>"
                            inputmode="decimal"
                            <?= $productDiscountEnabled ? 'required' : 'disabled' ?>
                            data-admin-discount-price
                        >
                        <small><?= e(t('admin.discount_price_help')) ?></small>
                    </div>
                </div>

                <div class="admin-field admin-field--full">
                    <label for="<?= e($productDialogId) ?>-image">
                        <?= e(t('admin.product_image')) ?>
                    </label>
                    <div class="admin-image-control">
                        <div class="admin-image-preview" data-admin-image-preview>
                            <?php if ($productImage !== ''): ?>
                                <img
                                    src="<?= e(url($productImage)) ?>"
                                    alt=""
                                    data-admin-image-preview-image
                                >
                            <?php else: ?>
                                <i class="fa-solid fa-image" aria-hidden="true" data-admin-image-preview-placeholder></i>
                            <?php endif; ?>
                        </div>
                        <div class="admin-image-fields">
                            <input
                                id="<?= e($productDialogId) ?>-image"
                                name="image_file"
                                type="file"
                                accept="image/png,.png"
                                data-admin-image-input
                            >
                            <small><?= e(t('admin.png_upload_help')) ?></small>
                            <?php if ($productImage !== ''): ?>
                                <small><?= e(t('admin.current_image')) ?>: <?= e($productImage) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="<?= e($productDialogId) ?>-tebex">
                        <?= e(t('admin.tebex_package_id')) ?>
                    </label>
                    <input
                        id="<?= e($productDialogId) ?>-tebex"
                        name="tebex_package_id"
                        value="<?= e((string) ($productForm['tebex_package_id'] ?? '')) ?>"
                        maxlength="64"
                    >
                </div>

                <div class="admin-field">
                    <label for="<?= e($productDialogId) ?>-sort">
                        <?= e(t('admin.sort_order')) ?>
                    </label>
                    <input
                        id="<?= e($productDialogId) ?>-sort"
                        name="sort_order"
                        type="number"
                        min="-10000"
                        max="10000"
                        value="<?= (int) $productForm['sort_order'] ?>"
                    >
                </div>

                <div class="admin-field admin-field--full">
                    <label for="<?= e($productDialogId) ?>-description">
                        <?= e(t('common.description')) ?>
                    </label>
                    <textarea
                        id="<?= e($productDialogId) ?>-description"
                        name="description"
                        rows="4"
                        maxlength="1000"
                    ><?= e((string) $productForm['description']) ?></textarea>
                </div>

                <label class="admin-check">
                    <input
                        name="active"
                        type="checkbox"
                        value="1"
                        <?= (bool) $productForm['active'] ? 'checked' : '' ?>
                    >
                    <span><?= e(t('common.active')) ?></span>
                </label>
            </div>
        </div>

        <footer class="admin-dialog-actions">
            <?php if (!$productIsNew): ?>
                <button
                    class="button admin-danger-button"
                    type="submit"
                    formaction="<?= e(url('actions/admin_delete_product.php')) ?>"
                    formnovalidate
                    data-delete-product
                >
                    <?= e(t('common.delete')) ?>
                </button>
            <?php endif; ?>

            <span class="admin-dialog-actions-spacer"></span>

            <button class="button button--ghost" type="button" data-dialog-close>
                <?= e(t('common.close')) ?>
            </button>

            <button
                class="button button--primary"
                type="submit"
                <?= !$hasCategoryOptions ? 'disabled' : '' ?>
            >
                <?= e($productIsNew ? t('admin.create_product') : t('common.save')) ?>
            </button>
        </footer>
    </form>
</dialog>
