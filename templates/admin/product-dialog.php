<?php

$productId = (int) $productForm['id'];
$productDialogId = $productId > 0
    ? 'admin-product-dialog-' . $productId
    : 'admin-product-dialog-new';
$productTitleId = $productDialogId . '-title';
$productIsNew = $productId === 0;

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
        <?php if (!$productIsNew): ?>
            data-confirm-delete="<?= e(t('admin.delete_product_confirm')) ?>"
        <?php endif; ?>
    >
        <?= csrf_field() ?>

        <input
            type="hidden"
            name="id"
            value="<?= $productId ?>"
        >

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
                <i
                    class="fa-solid fa-xmark"
                    aria-hidden="true"
                ></i>
            </button>
        </header>

        <div class="admin-dialog-body">
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
                        pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                        required
                    >
                </div>

                <div class="admin-field">
                    <label for="<?= e($productDialogId) ?>-category">
                        <?= e(t('common.category')) ?>
                    </label>

                    <select
                        id="<?= e($productDialogId) ?>-category"
                        name="category"
                    >
                        <?php foreach (STORE_CATEGORIES as $categoryName): ?>
                            <option
                                value="<?= e($categoryName) ?>"
                                <?= $productForm['category'] === $categoryName ? 'selected' : '' ?>
                            >
                                <?= e(localized_category($categoryName)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-field">
                    <label for="<?= e($productDialogId) ?>-price">
                        <?= e(t('admin.price_eur')) ?>
                    </label>

                    <input
                        id="<?= e($productDialogId) ?>-price"
                        name="price"
                        value="<?= e(
                            number_format(
                                (int) $productForm['price_cents'] / 100,
                                2,
                                '.',
                                ''
                            )
                        ) ?>"
                        inputmode="decimal"
                        required
                    >
                </div>

                <div class="admin-field">
                    <label for="<?= e($productDialogId) ?>-image">
                        <?= e(t('admin.image_path')) ?>
                    </label>

                    <input
                        id="<?= e($productDialogId) ?>-image"
                        name="image"
                        value="<?= e((string) $productForm['image']) ?>"
                        placeholder="assets/product.png"
                    >
                </div>

                <div class="admin-field">
                    <label for="<?= e($productDialogId) ?>-tebex">
                        <?= e(t('admin.tebex_package_id')) ?>
                    </label>

                    <input
                        id="<?= e($productDialogId) ?>-tebex"
                        name="tebex_package_id"
                        value="<?= e(
                            (string) ($productForm['tebex_package_id'] ?? '')
                        ) ?>"
                        maxlength="64"
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

                <div class="admin-field">
                    <label for="<?= e($productDialogId) ?>-sort">
                        <?= e(t('admin.sort_order')) ?>
                    </label>

                    <input
                        id="<?= e($productDialogId) ?>-sort"
                        name="sort_order"
                        type="number"
                        value="<?= (int) $productForm['sort_order'] ?>"
                    >
                </div>

                <label class="admin-check">
                    <input
                        name="active"
                        type="checkbox"
                        value="1"
                        <?= (bool) $productForm['active'] ? 'checked' : '' ?>
                    >

                    <span>
                        <?= e(t('common.active')) ?>
                    </span>
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

            <button
                class="button button--ghost"
                type="button"
                data-dialog-close
            >
                <?= e(t('common.close')) ?>
            </button>

            <button
                class="button button--primary"
                type="submit"
            >
                <?= e(
                    $productIsNew
                        ? t('admin.create_product')
                        : t('common.save')
                ) ?>
            </button>
        </footer>
    </form>
</dialog>
