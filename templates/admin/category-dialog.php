<?php

$categoryId = (int) ($categoryForm['id'] ?? 0);
$categoryIsNew = $categoryId === 0;
$categoryDialogId = $categoryIsNew
    ? 'admin-category-dialog-new'
    : 'admin-category-dialog-' . $categoryId;
$categoryTitleId = $categoryDialogId . '-title';
$categoryImage = (string) ($categoryForm['image'] ?? '');
$productCount = (int) ($categoryForm['product_count'] ?? 0);

?>

<dialog
    class="admin-dialog"
    id="<?= e($categoryDialogId) ?>"
    aria-labelledby="<?= e($categoryTitleId) ?>"
>
    <form
        class="admin-dialog-form"
        action="<?= e(url('actions/admin_save_category.php')) ?>"
        method="post"
        enctype="multipart/form-data"
        data-confirm-delete="<?= e(t('admin.delete_category_confirm')) ?>"
    >
        <?= csrf_field() ?>

        <input type="hidden" name="id" value="<?= $categoryId ?>">

        <header class="admin-dialog-header">
            <div>
                <span class="admin-dialog-kicker">
                    <?= e(t('admin.section_categories')) ?>
                </span>
                <h3 id="<?= e($categoryTitleId) ?>">
                    <?= e(
                        $categoryIsNew
                            ? t('admin.create_category')
                            : (string) $categoryForm['name']
                    ) ?>
                </h3>
                <?php if (!$categoryIsNew): ?>
                    <p><?= e(t('admin.category_products_count', ['count' => $productCount])) ?></p>
                <?php endif; ?>
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
            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="<?= e($categoryDialogId) ?>-name">
                        <?= e(t('admin.category_name')) ?>
                    </label>
                    <input
                        id="<?= e($categoryDialogId) ?>-name"
                        name="name"
                        value="<?= e((string) ($categoryForm['name'] ?? '')) ?>"
                        maxlength="80"
                        required
                        <?= $categoryIsNew ? 'data-admin-slug-source' : '' ?>
                    >
                </div>

                <div class="admin-field">
                    <label for="<?= e($categoryDialogId) ?>-slug">
                        <?= e(t('admin.slug')) ?>
                    </label>
                    <input
                        id="<?= e($categoryDialogId) ?>-slug"
                        name="slug"
                        value="<?= e((string) ($categoryForm['slug'] ?? '')) ?>"
                        maxlength="80"
                        pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                        required
                        <?= $categoryIsNew ? 'data-admin-slug-target' : 'readonly' ?>
                    >
                    <small><?= e(t('admin.category_slug_help')) ?></small>
                </div>

                <div class="admin-field">
                    <label for="<?= e($categoryDialogId) ?>-sort">
                        <?= e(t('admin.sort_order')) ?>
                    </label>
                    <input
                        id="<?= e($categoryDialogId) ?>-sort"
                        name="sort_order"
                        type="number"
                        min="-10000"
                        max="10000"
                        value="<?= (int) ($categoryForm['sort_order'] ?? 0) ?>"
                    >
                    <small><?= e(t('admin.category_sort_help')) ?></small>
                </div>

                <label class="admin-check">
                    <input
                        name="active"
                        type="checkbox"
                        value="1"
                        <?= (bool) ($categoryForm['active'] ?? true) ? 'checked' : '' ?>
                    >
                    <span><?= e(t('common.active')) ?></span>
                </label>

                <div class="admin-field admin-field--full">
                    <label for="<?= e($categoryDialogId) ?>-image">
                        <?= e(t('admin.category_image')) ?>
                    </label>
                    <div class="admin-image-control">
                        <div class="admin-image-preview" data-admin-image-preview>
                            <?php if ($categoryImage !== ''): ?>
                                <img
                                    src="<?= e(url($categoryImage)) ?>"
                                    alt=""
                                    data-admin-image-preview-image
                                >
                            <?php else: ?>
                                <i class="fa-solid fa-image" aria-hidden="true" data-admin-image-preview-placeholder></i>
                            <?php endif; ?>
                        </div>
                        <div class="admin-image-fields">
                            <input
                                id="<?= e($categoryDialogId) ?>-image"
                                name="image_file"
                                type="file"
                                accept="image/png,.png"
                                data-admin-image-input
                                <?= $categoryIsNew ? 'required' : '' ?>
                            >
                            <small><?= e(t('admin.png_upload_help')) ?></small>
                            <?php if (!$categoryIsNew && $categoryImage !== ''): ?>
                                <small><?= e(t('admin.current_image')) ?>: <?= e($categoryImage) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!$categoryIsNew && $productCount > 0): ?>
                <div class="admin-inline-note">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    <span><?= e(t('admin.category_delete_requires_empty')) ?></span>
                    <a href="<?= e(admin_section_url('products', ['category_id' => $categoryId])) ?>">
                        <?= e(t('admin.view_category_products')) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <footer class="admin-dialog-actions">
            <?php if (!$categoryIsNew): ?>
                <button
                    class="button admin-danger-button"
                    type="submit"
                    formaction="<?= e(url('actions/admin_delete_category.php')) ?>"
                    formnovalidate
                    data-delete-category
                    <?= $productCount > 0 ? 'disabled' : '' ?>
                >
                    <?= e(t('common.delete')) ?>
                </button>
            <?php endif; ?>

            <span class="admin-dialog-actions-spacer"></span>

            <button class="button button--ghost" type="button" data-dialog-close>
                <?= e(t('common.close')) ?>
            </button>
            <button class="button button--primary" type="submit">
                <?= e($categoryIsNew ? t('admin.create_category') : t('common.save')) ?>
            </button>
        </footer>
    </form>
</dialog>
