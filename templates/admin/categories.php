<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.categories')) ?></h2>
        <p><?= e(t('admin.categories_text')) ?></p>
    </div>
</section>

<div class="admin-category-grid">
    <?php foreach ($adminCategories as $category): ?>
        <form
            class="admin-panel admin-category-card"
            action="<?= e(url('actions/admin_save_category.php')) ?>"
            method="post"
        >
            <?= csrf_field() ?>

            <input
                type="hidden"
                name="category"
                value="<?= e($category['key']) ?>"
            >

            <div class="admin-category-preview">
                <img
                    src="<?= e(url($category['image'])) ?>"
                    alt="<?= e($category['name']) ?>"
                >
            </div>

            <div class="admin-category-card-body">
                <p class="admin-category-key">
                    <?= e($category['key']) ?>
                </p>

                <div class="admin-field">
                    <label for="category-<?= e($category['key']) ?>-name">
                        <?= e(t('admin.category_name')) ?>
                    </label>

                    <input
                        id="category-<?= e($category['key']) ?>-name"
                        name="name"
                        value="<?= e($category['name']) ?>"
                        maxlength="80"
                        required
                    >
                </div>

                <div class="admin-field">
                    <label for="category-<?= e($category['key']) ?>-image">
                        <?= e(t('admin.category_image')) ?>
                    </label>

                    <input
                        id="category-<?= e($category['key']) ?>-image"
                        name="image"
                        value="<?= e($category['image']) ?>"
                        maxlength="255"
                        placeholder="assets/category.png"
                        required
                    >

                    <small><?= e(t('admin.category_image_help')) ?></small>
                </div>

                <button class="button button--primary" type="submit">
                    <?= e(t('common.save')) ?>
                </button>
            </div>
        </form>
    <?php endforeach; ?>
</div>

<section class="admin-panel admin-category-limit-note">
    <i class="fa-solid fa-lock" aria-hidden="true"></i>
    <p><?= e(t('admin.categories_locked')) ?></p>
</section>
