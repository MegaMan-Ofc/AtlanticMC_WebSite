<section class="admin-section-heading">
    <div>
        <h2><?= e(t('admin.recommended')) ?></h2>
        <p><?= e(t('admin.recommended_text')) ?></p>
    </div>

    <span class="admin-result-count">
        <?= e(t('admin.recommended_count', [
            'count' => count(array_filter($adminRecommendedSlots)),
            'limit' => RECOMMENDED_PRODUCT_SLOTS,
        ])) ?>
    </span>
</section>

<section class="admin-panel admin-recommended-panel">
    <div class="admin-recommended-toolbar">
        <div>
            <strong><?= e(t('admin.recommended_preview')) ?></strong>
            <p><?= e(t('admin.recommended_drag_help')) ?></p>
        </div>
        <span class="admin-recommended-save-state" data-admin-recommended-state aria-live="polite"></span>
    </div>

    <div
        class="admin-recommended-grid"
        data-admin-recommended-grid
        data-reorder-url="<?= e(url('actions/admin_reorder_recommended.php')) ?>"
        data-csrf-token="<?= e(csrf_token()) ?>"
        data-saving-label="<?= e(t('admin.recommended_saving')) ?>"
        data-saved-label="<?= e(t('admin.recommended_saved')) ?>"
        data-error-label="<?= e(t('admin.recommended_save_error')) ?>"
    >
        <?php foreach ($adminRecommendedSlots as $slot => $product): ?>
            <?php $localizedProduct = is_array($product) ? localized_product($product) : null; ?>
            <article
                class="admin-recommended-slot<?= is_array($product) ? ' has-product' : ' is-empty' ?>"
                data-admin-recommended-slot
                data-product-id="<?= is_array($product) ? (int) $product['id'] : 0 ?>"
                <?= is_array($product) ? 'draggable="true"' : '' ?>
            >
                <header class="admin-recommended-slot-header">
                    <span class="admin-recommended-slot-number" data-admin-recommended-slot-label>
                        <?= e(t('admin.recommended_slot', ['slot' => $slot])) ?>
                    </span>
                    <?php if (is_array($product)): ?>
                        <span class="admin-recommended-drag-handle" title="<?= e(t('admin.recommended_drag')) ?>">
                            <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
                        </span>
                    <?php endif; ?>
                </header>

                <?php if (is_array($product) && is_array($localizedProduct)): ?>
                    <div class="admin-recommended-product-preview">
                        <div class="admin-recommended-image">
                            <img src="<?= e(url((string) $product['image'])) ?>" alt="" loading="lazy">
                        </div>
                        <div class="admin-recommended-product-copy">
                            <small><?= e((string) $product['category_name']) ?></small>
                            <strong><?= e((string) $localizedProduct['name']) ?></strong>
                            <div class="admin-recommended-price">
                                <?php if (product_has_discount($product)): ?>
                                    <span><?= e(format_money((int) $product['price_cents'], (string) $product['currency'])) ?></span>
                                <?php endif; ?>
                                <b><?= e(format_money(product_effective_price_cents($product), (string) $product['currency'])) ?></b>
                            </div>
                        </div>
                    </div>

                    <?php if (!(bool) $product['active'] || !(bool) $product['category_active']): ?>
                        <div class="admin-inline-note admin-inline-note--warning">
                            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                            <span><?= e(t('admin.recommended_inactive_warning')) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="admin-recommended-actions">
                        <button
                            class="button button--ghost"
                            type="button"
                            data-admin-recommended-edit
                            data-slot="<?= $slot ?>"
                            data-product-id="<?= (int) $product['id'] ?>"
                        >
                            <i class="fa-solid fa-arrow-right-arrow-left" aria-hidden="true"></i>
                            <?= e(t('admin.recommended_replace')) ?>
                        </button>

                        <form action="<?= e(url('actions/admin_remove_recommended.php')) ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="slot" value="<?= $slot ?>" data-admin-recommended-slot-input>
                            <button class="button button--danger" type="submit">
                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                <?= e(t('common.delete')) ?>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <button
                        class="admin-recommended-empty-action"
                        type="button"
                        data-admin-recommended-edit
                        data-slot="<?= $slot ?>"
                        data-product-id="0"
                    >
                        <span class="admin-recommended-empty-icon">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        </span>
                        <strong><?= e(t('admin.recommended_add')) ?></strong>
                        <small><?= e(t('admin.recommended_empty_slot')) ?></small>
                    </button>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<dialog class="admin-dialog" id="admin-recommended-dialog" aria-labelledby="admin-recommended-dialog-title">
    <form class="admin-dialog-form" action="<?= e(url('actions/admin_save_recommended.php')) ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="slot" value="1" data-admin-recommended-dialog-slot>

        <header class="admin-dialog-header">
            <div>
                <span class="admin-dialog-kicker"><?= e(t('admin.section_recommended')) ?></span>
                <h3 id="admin-recommended-dialog-title" data-admin-recommended-dialog-title>
                    <?= e(t('admin.recommended_choose_product')) ?>
                </h3>
            </div>
            <button class="admin-dialog-close" type="button" data-dialog-close aria-label="<?= e(t('common.close')) ?>">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </header>

        <div class="admin-dialog-body">
            <?php if ($adminRecommendedOptions === []): ?>
                <div class="admin-inline-note admin-inline-note--warning">
                    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                    <span><?= e(t('admin.recommended_no_products')) ?></span>
                </div>
            <?php else: ?>
                <div class="admin-field">
                    <label for="admin-recommended-product-select"><?= e(t('common.product')) ?></label>
                    <select id="admin-recommended-product-select" name="product_id" required data-admin-recommended-product-select>
                        <?php foreach ($adminRecommendedOptions as $option): ?>
                            <?php $localizedOption = localized_product($option); ?>
                            <option value="<?= (int) $option['id'] ?>">
                                <?= e((string) $option['category_name']) ?> · <?= e((string) $localizedOption['name']) ?> · <?= e(format_money(product_effective_price_cents($option), (string) $option['currency'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small><?= e(t('admin.recommended_choose_help')) ?></small>
                </div>
            <?php endif; ?>
        </div>

        <footer class="admin-dialog-actions">
            <button class="button button--ghost" type="button" data-dialog-close><?= e(t('common.close')) ?></button>
            <span class="admin-dialog-actions-spacer"></span>
            <button class="button button--primary" type="submit" <?= $adminRecommendedOptions === [] ? 'disabled' : '' ?>>
                <?= e(t('common.save')) ?>
            </button>
        </footer>
    </form>
</dialog>

<section class="admin-section-heading admin-home-layout-heading">
    <div>
        <h2><?= e(t('admin.home_layout_title')) ?></h2>
        <p><?= e(t('admin.home_layout_text')) ?></p>
    </div>

    <span class="admin-recommended-save-state" data-admin-home-layout-state aria-live="polite"></span>
</section>

<section
    class="admin-panel admin-home-layout-panel"
    data-admin-home-layout
    data-save-url="<?= e(url('actions/admin_save_home_categories.php')) ?>"
    data-csrf-token="<?= e(csrf_token()) ?>"
    data-saving-label="<?= e(t('admin.home_layout_saving')) ?>"
    data-saved-label="<?= e(t('admin.home_layout_saved')) ?>"
    data-error-label="<?= e(t('admin.home_layout_save_error')) ?>"
>
    <div class="admin-home-layout-zone-block admin-home-layout-zone-block--banner">
        <header class="admin-home-layout-zone-heading">
            <div>
                <span class="admin-home-layout-zone-kicker"><?= e(t('admin.home_top_banner')) ?></span>
                <strong><?= e(t('admin.home_top_banner_help')) ?></strong>
            </div>
            <div class="admin-home-layout-zone-actions">
                <button
                    class="admin-home-banner-edit"
                    type="button"
                    data-admin-home-banner-edit
                    data-zone="top"
                    title="<?= e(t('admin.home_banner_edit')) ?>"
                    aria-label="<?= e(t('admin.home_banner_edit')) ?>"
                    <?= is_array($adminHomeCategoryLayout['top']) ? '' : 'disabled' ?>
                >
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                </button>
            </div>
        </header>

        <div class="admin-home-banner-zone" data-admin-home-zone="top">
            <?php if (is_array($adminHomeCategoryLayout['top'])): ?>
                <?php
                $adminHomeCategory = $adminHomeCategoryLayout['top'];
                require BASE_PATH . '/templates/admin/home-category-card.php';
                ?>
            <?php endif; ?>
            <div class="admin-home-zone-empty" data-admin-home-empty>
                <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                <span><?= e(t('admin.home_banner_empty')) ?></span>
            </div>
        </div>
    </div>

    <div class="admin-home-layout-zone-block">
        <header class="admin-home-layout-zone-heading">
            <div>
                <span class="admin-home-layout-zone-kicker"><?= e(t('admin.home_category_grid')) ?></span>
                <strong><?= e(t('admin.home_category_grid_help')) ?></strong>
            </div>
            <i class="fa-solid fa-grip" aria-hidden="true"></i>
        </header>

        <div class="admin-home-category-grid" data-admin-home-zone="grid">
            <?php foreach ($adminHomeCategoryLayout['grid'] as $category): ?>
                <?php
                $adminHomeCategory = $category;
                require BASE_PATH . '/templates/admin/home-category-card.php';
                ?>
            <?php endforeach; ?>
            <div class="admin-home-zone-empty admin-home-zone-empty--grid" data-admin-home-empty>
                <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                <span><?= e(t('admin.home_grid_empty')) ?></span>
            </div>
        </div>
    </div>

    <div class="admin-home-layout-zone-block admin-home-layout-zone-block--banner">
        <header class="admin-home-layout-zone-heading">
            <div>
                <span class="admin-home-layout-zone-kicker"><?= e(t('admin.home_bottom_banner')) ?></span>
                <strong><?= e(t('admin.home_bottom_banner_help')) ?></strong>
            </div>
            <div class="admin-home-layout-zone-actions">
                <button
                    class="admin-home-banner-edit"
                    type="button"
                    data-admin-home-banner-edit
                    data-zone="bottom"
                    title="<?= e(t('admin.home_banner_edit')) ?>"
                    aria-label="<?= e(t('admin.home_banner_edit')) ?>"
                    <?= is_array($adminHomeCategoryLayout['bottom']) ? '' : 'disabled' ?>
                >
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                </button>
            </div>
        </header>

        <div class="admin-home-banner-zone" data-admin-home-zone="bottom">
            <?php if (is_array($adminHomeCategoryLayout['bottom'])): ?>
                <?php
                $adminHomeCategory = $adminHomeCategoryLayout['bottom'];
                require BASE_PATH . '/templates/admin/home-category-card.php';
                ?>
            <?php endif; ?>
            <div class="admin-home-zone-empty" data-admin-home-empty>
                <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                <span><?= e(t('admin.home_banner_empty')) ?></span>
            </div>
        </div>
    </div>

    <div class="admin-inline-note admin-home-layout-note">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        <span><?= e(t('admin.home_layout_note')) ?></span>
    </div>
</section>


<dialog
    class="admin-dialog admin-home-banner-dialog"
    id="admin-home-banner-dialog"
    aria-labelledby="admin-home-banner-dialog-title"
    data-default-kicker="<?= e(t('home.category_banner_kicker')) ?>"
    data-default-cta="<?= e(t('home.category_banner_action')) ?>"
>
    <form
        class="admin-dialog-form"
        action="<?= e(url('actions/admin_save_home_banner.php')) ?>"
        method="post"
        data-admin-home-banner-form
    >
        <?= csrf_field() ?>
        <input type="hidden" name="category_id" value="0" data-admin-home-banner-category-id>

        <header class="admin-dialog-header">
            <div>
                <span class="admin-dialog-kicker"><?= e(t('admin.home_banner_dialog_kicker')) ?></span>
                <h3 id="admin-home-banner-dialog-title"><?= e(t('admin.home_banner_dialog_title')) ?></h3>
                <p data-admin-home-banner-dialog-category></p>
            </div>
            <button class="admin-dialog-close" type="button" data-dialog-close aria-label="<?= e(t('common.close')) ?>">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </header>

        <div class="admin-dialog-body admin-home-banner-dialog-body">
            <section class="admin-home-banner-preview-wrap">
                <div
                    class="admin-home-banner-preview"
                    data-admin-home-banner-preview
                    data-style="auto"
                    data-image-side="right"
                    data-image-size="normal"
                >
                    <span class="admin-home-banner-preview-watermark" data-admin-home-banner-preview-watermark></span>
                    <div class="admin-home-banner-preview-copy">
                        <small data-admin-home-banner-preview-kicker></small>
                        <strong data-admin-home-banner-preview-title></strong>
                        <p data-admin-home-banner-preview-text hidden></p>
                        <span data-admin-home-banner-preview-cta>
                            <span data-admin-home-banner-preview-cta-label></span>
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="admin-home-banner-preview-image">
                        <img src="" alt="" data-admin-home-banner-preview-image>
                    </div>
                </div>
                <p><?= e(t('admin.home_banner_preview_help')) ?></p>
            </section>

            <div class="admin-home-banner-form-grid">
                <section class="admin-home-banner-form-section">
                    <div class="admin-home-banner-form-heading">
                        <i class="fa-solid fa-font" aria-hidden="true"></i>
                        <div>
                            <strong><?= e(t('admin.home_banner_content')) ?></strong>
                            <small><?= e(t('admin.home_banner_content_help')) ?></small>
                        </div>
                    </div>

                    <div class="admin-field">
                        <label for="admin-home-banner-kicker"><?= e(t('admin.home_banner_kicker_label')) ?></label>
                        <input id="admin-home-banner-kicker" name="kicker" type="text" maxlength="80" data-admin-home-banner-input="kicker">
                        <small><?= e(t('admin.home_banner_kicker_help')) ?></small>
                    </div>

                    <div class="admin-field">
                        <label for="admin-home-banner-title"><?= e(t('admin.home_banner_title_label')) ?></label>
                        <input id="admin-home-banner-title" name="title" type="text" maxlength="120" data-admin-home-banner-input="title">
                        <small><?= e(t('admin.home_banner_title_help')) ?></small>
                    </div>

                    <div class="admin-field">
                        <label for="admin-home-banner-text"><?= e(t('admin.home_banner_text_label')) ?></label>
                        <textarea id="admin-home-banner-text" name="text" rows="3" maxlength="255" data-admin-home-banner-input="text"></textarea>
                        <small><?= e(t('admin.home_banner_text_help')) ?></small>
                    </div>

                    <div class="admin-field">
                        <label for="admin-home-banner-cta"><?= e(t('admin.home_banner_cta_label')) ?></label>
                        <input id="admin-home-banner-cta" name="cta" type="text" maxlength="80" data-admin-home-banner-input="cta">
                        <small><?= e(t('admin.home_banner_cta_help')) ?></small>
                    </div>
                </section>

                <section class="admin-home-banner-form-section">
                    <div class="admin-home-banner-form-heading">
                        <i class="fa-solid fa-palette" aria-hidden="true"></i>
                        <div>
                            <strong><?= e(t('admin.home_banner_appearance')) ?></strong>
                            <small><?= e(t('admin.home_banner_appearance_help')) ?></small>
                        </div>
                    </div>

                    <div class="admin-field">
                        <label for="admin-home-banner-style"><?= e(t('admin.home_banner_style_label')) ?></label>
                        <select id="admin-home-banner-style" name="style" data-admin-home-banner-input="style">
                            <?php foreach (home_banner_style_options() as $style): ?>
                                <option value="<?= e($style) ?>"><?= e(t('admin.home_banner_style_' . $style)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="admin-home-banner-inline-fields">
                        <div class="admin-field">
                            <label for="admin-home-banner-image-side"><?= e(t('admin.home_banner_image_side_label')) ?></label>
                            <select id="admin-home-banner-image-side" name="image_side" data-admin-home-banner-input="image_side">
                                <option value="right"><?= e(t('admin.home_banner_image_side_right')) ?></option>
                                <option value="left"><?= e(t('admin.home_banner_image_side_left')) ?></option>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="admin-home-banner-image-size"><?= e(t('admin.home_banner_image_size_label')) ?></label>
                            <select id="admin-home-banner-image-size" name="image_size" data-admin-home-banner-input="image_size">
                                <option value="compact"><?= e(t('admin.home_banner_image_size_compact')) ?></option>
                                <option value="normal"><?= e(t('admin.home_banner_image_size_normal')) ?></option>
                                <option value="large"><?= e(t('admin.home_banner_image_size_large')) ?></option>
                            </select>
                        </div>
                    </div>

                    <label class="admin-home-banner-switch">
                        <input type="checkbox" name="show_watermark" value="1" data-admin-home-banner-input="show_watermark">
                        <span>
                            <strong><?= e(t('admin.home_banner_watermark_label')) ?></strong>
                            <small><?= e(t('admin.home_banner_watermark_help')) ?></small>
                        </span>
                    </label>

                    <label class="admin-home-banner-switch">
                        <input type="checkbox" name="show_cta" value="1" data-admin-home-banner-input="show_cta">
                        <span>
                            <strong><?= e(t('admin.home_banner_show_cta_label')) ?></strong>
                            <small><?= e(t('admin.home_banner_show_cta_help')) ?></small>
                        </span>
                    </label>
                </section>
            </div>

            <div class="admin-inline-note admin-inline-note--info admin-home-banner-note">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                <span><?= e(t('admin.home_banner_note')) ?></span>
            </div>
        </div>

        <footer class="admin-dialog-actions">
            <button class="button button--ghost" type="button" data-admin-home-banner-reset>
                <i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>
                <?= e(t('admin.home_banner_reset')) ?>
            </button>
            <span class="admin-home-banner-form-state" data-admin-home-banner-form-state aria-live="polite"></span>
            <span class="admin-dialog-actions-spacer"></span>
            <button class="button button--ghost" type="button" data-dialog-close><?= e(t('common.close')) ?></button>
            <button class="button button--primary" type="submit">
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                <?= e(t('common.save')) ?>
            </button>
        </footer>
    </form>
</dialog>
