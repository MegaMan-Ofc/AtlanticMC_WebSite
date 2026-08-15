<main class="main-content" id="main">
    <div class="container">
        <header class="page-title search-page-title">
            <a aria-label="<?= e(t('common.back_to_store')) ?>" href="<?= e(route_url('home')) ?>"><i class="fa-solid fa-house" aria-hidden="true"></i></a>
            <div>
                <h1><?= e(t('search.title')) ?></h1>
                <p class="page-subtitle"><?= e(t('search.description')) ?></p>
            </div>
        </header>

        <section class="search-panel" aria-label="<?= e(t('search.controls_aria')) ?>">
            <form class="search-form" action="<?= e(route_url('search')) ?>" method="get" role="search">
                <label class="sr-only" for="product-search"><?= e(t('search.input_label')) ?></label>
                <div class="search-input-wrap">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input
                        id="product-search"
                        name="q"
                        type="search"
                        maxlength="80"
                        value="<?= e($searchQuery) ?>"
                        placeholder="<?= e(t('search.placeholder')) ?>"
                        autocomplete="off"
                    >
                </div>
                <?php if ($searchCategory !== ''): ?><input type="hidden" name="category" value="<?= e($searchCategory) ?>"><?php endif; ?>
                <?php if ($searchDiscountOnly): ?><input type="hidden" name="discount" value="1"><?php endif; ?>
                <?php if ($searchSort !== 'relevance'): ?><input type="hidden" name="sort" value="<?= e($searchSort) ?>"><?php endif; ?>
                <button class="button button--primary search-submit" type="submit"><?= e(t('search.submit')) ?></button>
            </form>

            <div class="search-filter-group">
                <span class="search-filter-label"><?= e(t('search.filter_category')) ?></span>
                <div class="search-filter-chips">
                    <a class="search-filter-chip<?= $searchCategory === '' ? ' is-active' : '' ?>" href="<?= e(route_url('search', product_search_query_parameters($searchQuery, '', $searchDiscountOnly, $searchSort))) ?>"><?= e(t('search.all_categories')) ?></a>
                    <?php foreach ($searchCategories as $filterCategory): ?>
                        <?php $filterSlug = (string) $filterCategory['slug']; ?>
                        <a class="search-filter-chip<?= $searchCategory === $filterSlug ? ' is-active' : '' ?>" href="<?= e(route_url('search', product_search_query_parameters($searchQuery, $filterSlug, $searchDiscountOnly, $searchSort))) ?>"><?= e((string) $filterCategory['name']) ?></a>
                    <?php endforeach; ?>
                    <a class="search-filter-chip search-filter-chip--accent<?= $searchDiscountOnly ? ' is-active' : '' ?>" href="<?= e(route_url('search', product_search_query_parameters($searchQuery, $searchCategory, !$searchDiscountOnly, $searchSort))) ?>">
                        <i class="fa-solid fa-tag" aria-hidden="true"></i>
                        <?= e(t('search.only_discounts')) ?>
                    </a>
                </div>
            </div>

            <div class="search-filter-group">
                <span class="search-filter-label"><?= e(t('search.sort_by')) ?></span>
                <div class="search-filter-chips">
                    <?php foreach ([
                        'relevance' => t('search.sort_relevance'),
                        'price-asc' => t('search.sort_price_asc'),
                        'price-desc' => t('search.sort_price_desc'),
                        'name' => t('search.sort_name'),
                    ] as $sortKey => $sortLabel): ?>
                        <a class="search-filter-chip<?= $searchSort === $sortKey ? ' is-active' : '' ?>" href="<?= e(route_url('search', product_search_query_parameters($searchQuery, $searchCategory, $searchDiscountOnly, $sortKey))) ?>"><?= e($sortLabel) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <div class="search-results-heading">
            <div>
                <span class="search-results-count"><?= e(t('search.results_count', ['count' => count($products)])) ?></span>
                <?php if ($searchQuery !== ''): ?><strong><?= e(t('search.results_for', ['query' => $searchQuery])) ?></strong><?php else: ?><strong><?= e(t('search.all_products')) ?></strong><?php endif; ?>
            </div>
            <?php if ($searchQuery !== '' || $searchCategory !== '' || $searchDiscountOnly || $searchSort !== 'relevance'): ?>
                <a class="search-clear" href="<?= e(route_url('search')) ?>"><?= e(t('search.clear_filters')) ?></a>
            <?php endif; ?>
        </div>

        <?php if ($products === []): ?>
            <section class="search-empty prose">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <h2><?= e(t('search.no_results')) ?></h2>
                <p><?= e(t('search.no_results_text')) ?></p>
                <a class="button button--ghost" href="<?= e(route_url('search')) ?>"><?= e(t('search.show_all')) ?></a>
            </section>
        <?php else: ?>
            <section class="catalog-grid search-results-grid" aria-label="<?= e(t('search.results_aria')) ?>">
                <?php foreach ($products as $product): ?>
                    <?php require __DIR__ . '/product-card.php'; ?>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </div>
</main>
