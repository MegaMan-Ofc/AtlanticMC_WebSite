<?php

declare(strict_types=1);

    $ranks = store_category_by_slug('ranks', true);
    $assert(is_array($ranks), 'Existing category slugs are migrated to category records.');
    save_category_from_admin([
        'id' => (int) $ranks['id'],
        'slug' => 'ranks',
        'name' => 'Premium Ranks',
        'sort_order' => 10,
        'active' => '1',
    ], 'assets/diamante.png');
    $assert(
        store_category_name('ranks') === 'Premium Ranks'
            && store_category_image('ranks') === 'assets/diamante.png',
        'Administrators can edit dynamic category data.'
    );

    $testCategoryId = save_category_from_admin([
        'slug' => 'test-category',
        'name' => 'Test Category',
        'sort_order' => 90,
        'active' => '1',
    ], 'assets/diamante.png');
    $assert(store_category_by_id($testCategoryId, true) !== null, 'Administrators can create categories.');
    $homeCategorySlugs = array_column(home_store_categories(), 'slug');
    $assert(
        in_array('test-category', $homeCategorySlugs, true)
            && category_configuration('test-category')['heading'] === 'Test Category',
        'Active dynamic categories appear on the homepage and receive a generic catalogue page configuration.'
    );

    $testProductId = save_product_from_admin([
        'category_id' => $testCategoryId,
        'name' => 'Dynamic Product',
        'slug' => 'dynamic-product',
        'description' => 'Dynamic category product.',
        'price' => '4.99',
        'sort_order' => 10,
        'active' => '1',
        'tebex_package_id' => '',
    ]);
    $testProduct = product_by_id($testProductId, true);
    $assert(
        is_array($testProduct)
            && (int) $testProduct['category_id'] === $testCategoryId
            && (string) $testProduct['category'] === 'test-category',
        'Products are linked to categories by ID while preserving the category slug mirror.'
    );

    save_product_from_admin([
        'id' => $testProductId,
        'category_id' => $testCategoryId,
        'name' => 'Dynamic Product',
        'slug' => 'dynamic-product',
        'description' => 'Dynamic category product.',
        'price' => '4.99',
        'discount_enabled' => '1',
        'discount_price' => '3.49',
        'sort_order' => 10,
        'active' => '1',
        'tebex_package_id' => '',
    ]);

    $discountedProduct = product_by_id($testProductId, true);

    $assert(
        is_array($discountedProduct)
            && (int) $discountedProduct['price_cents'] === 499
            && (int) $discountedProduct['discount_price_cents'] === 349
            && product_effective_price_cents($discountedProduct) === 349,
        'Administrators can enable a promotional price while keeping the regular product price.'
    );

    cart_clear();
    cart_add($testProductId, 1);
    $discountedSummary = cart_summary();

    $assert(
        (int) $discountedSummary['subtotal_cents'] === 349,
        'Cart totals use the promotional product price when a discount is active.'
    );

    $throws(
        static fn () => save_product_from_admin([
            'id' => $testProductId,
            'category_id' => $testCategoryId,
            'name' => 'Dynamic Product',
            'slug' => 'dynamic-product',
            'description' => 'Dynamic category product.',
            'price' => '4.99',
            'discount_enabled' => '1',
            'discount_price' => '5.00',
            'sort_order' => 10,
            'active' => '1',
            'tebex_package_id' => '',
        ]),
        'A promotional product price must be lower than the regular price.'
    );

    save_product_from_admin([
        'id' => $testProductId,
        'category_id' => $testCategoryId,
        'name' => 'Dynamic Product',
        'slug' => 'dynamic-product',
        'description' => 'Dynamic category product.',
        'price' => '4.99',
        'discount_enabled' => '0',
        'sort_order' => 10,
        'active' => '1',
        'tebex_package_id' => '',
    ]);

    $discountDisabledProduct = product_by_id($testProductId, true);

    $assert(
        is_array($discountDisabledProduct)
            && $discountDisabledProduct['discount_price_cents'] === null
            && product_effective_price_cents($discountDisabledProduct) === 499,
        'Disabling a product discount restores the regular price.'
    );

    cart_clear();
    $throws(
        static fn () => delete_category_from_admin($testCategoryId),
        'Categories containing products cannot be deleted.'
    );
    save_category_from_admin([
        'id' => $testCategoryId,
        'slug' => 'test-category',
        'name' => 'Test Category',
        'sort_order' => 90,
    ]);
    $assert(
        !in_array('test-category', array_column(home_store_categories(), 'slug'), true)
            && product_by_id($testProductId) === null,
        'Inactive categories disappear from the public homepage and hide their products.'
    );
    save_category_from_admin([
        'id' => $testCategoryId,
        'slug' => 'test-category',
        'name' => 'Test Category',
        'sort_order' => 90,
        'active' => '1',
    ]);
    delete_product_from_admin($testProductId);
    delete_category_from_admin($testCategoryId);
    $assert(store_category_by_id($testCategoryId, true) === null, 'Empty categories can be deleted.');
    $throws(
        static fn () => delete_category_from_admin((int) $ranks['id']),
        'Seed categories containing products cannot be deleted.'
    );
    $product = $pdo->query('SELECT * FROM products ORDER BY id LIMIT 1')->fetch();
    $productId = (int) $product['id'];
    $expectedPrice = (int) $product['price_cents'];
    cart_add($productId, 1);
    $summary = cart_summary();
    $assert((int) $summary['subtotal_cents'] === $expectedPrice, 'Cart totals use the database price.');

    $statement = $pdo->prepare('UPDATE products SET active = 0 WHERE id = :id');
    $statement->execute(['id' => $productId]);
    cart_clear();
    $throws(static fn () => cart_add($productId, 1), 'Inactive products cannot be added to the cart.');
