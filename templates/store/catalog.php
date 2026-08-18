<main class="main-content" id="main">
    <div class="container">
        <header class="page-title">
            <a aria-label="<?= e(t('catalog.go_back')) ?>" href="<?= e(route_url('home')) ?>"><i class="fa-solid fa-house" aria-hidden="true"></i></a>
            <div>
                <h1 id="page-title"><?= e($pageHeading) ?></h1>
                <p class="page-subtitle"><?= e($pageDescription) ?></p>
            </div>
        </header>

        <?php if ($products === []): ?>
            <section class="prose" aria-labelledby="page-title">
                <h2><?= e(t('catalog.no_products')) ?></h2>
                <p><?= e(t('catalog.no_products_text')) ?></p>
            </section>
        <?php else: ?>
            <section class="catalog-grid" aria-labelledby="page-title">
                <?php foreach ($products as $product): ?>
                    <?php require TEMPLATE_PATH . '/components/product-card.php'; ?>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </div>
</main>
