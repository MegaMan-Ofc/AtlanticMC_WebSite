<nav class="admin-navigation" aria-label="<?= e(t('admin.navigation')) ?>">
    <?php foreach (ADMIN_SECTIONS as $section): ?>
        <a class="admin-navigation-link<?= $adminSection === $section ? ' is-active' : '' ?>" href="<?= e(admin_section_url($section)) ?>">
            <i class="<?= e(admin_section_icon($section)) ?>" aria-hidden="true"></i>
            <span><?= e(t('admin.section_' . $section)) ?></span>
        </a>
    <?php endforeach; ?>
</nav>
