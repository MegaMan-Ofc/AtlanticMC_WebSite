<?php

declare(strict_types=1);

function seed_store_database(PDO $pdo): void
{
    $seedVersion = '5';
    $now = now_sql();
    $categories = [
        ['slug' => 'ranks', 'name' => 'Ranks', 'image' => 'assets/diamante.png', 'sort_order' => 10, 'home_placement' => 'top', 'home_sort_order' => 10],
        ['slug' => 'rubis', 'name' => 'Rubis', 'image' => 'assets/rubis-saco-pequeno.png.png', 'sort_order' => 20, 'home_placement' => 'grid', 'home_sort_order' => 20],
        ['slug' => 'keys', 'name' => 'Chaves', 'image' => 'assets/atlantic-key.png', 'sort_order' => 30, 'home_placement' => 'grid', 'home_sort_order' => 30],
        ['slug' => 'boosters', 'name' => 'Corações', 'image' => 'assets/heart (2).png', 'sort_order' => 40, 'home_placement' => 'grid', 'home_sort_order' => 40],
    ];

    $selectCategory = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug');
    $insertCategory = $pdo->prepare(
        'INSERT INTO categories (slug, name, image, active, sort_order, home_placement, home_sort_order, created_at, updated_at)
         VALUES (:slug, :name, :image, 1, :sort_order, :home_placement, :home_sort_order, :created_at, :updated_at)'
    );

    foreach ($categories as $category) {
        $selectCategory->execute(['slug' => $category['slug']]);

        if ($selectCategory->fetchColumn() !== false) {
            continue;
        }

        $insertCategory->execute([
            'slug' => $category['slug'],
            'name' => $category['name'],
            'image' => $category['image'],
            'sort_order' => $category['sort_order'],
            'home_placement' => $category['home_placement'],
            'home_sort_order' => $category['home_sort_order'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    $products = [
        ['slug' => 'deep-diver', 'category' => 'ranks', 'name' => 'Deep Diver', 'description' => 'Starter rank with useful commands and a starter kit.', 'image' => 'assets/deep diver.png', 'price_cents' => 1499, 'discount_price_cents' => null, 'sort_order' => 10, 'metadata' => ['color' => '#48b9d4', 'features' => ['Starter kit', 'Priority queue', 'Useful starter commands']]],
        ['slug' => 'sailor', 'category' => 'ranks', 'name' => 'Sailor', 'description' => 'Expanded permissions and a stronger recurring kit.', 'image' => 'assets/sailor.png', 'price_cents' => 2499, 'discount_price_cents' => null, 'sort_order' => 20, 'metadata' => ['color' => '#4f8ee8', 'features' => ['Sailor kit', 'More homes', 'Queue priority']]],
        ['slug' => 'sea-hunter', 'category' => 'ranks', 'name' => 'Sea Hunter', 'description' => 'Advanced rank for active Atlantic players.', 'image' => 'assets/sea hunter.png', 'price_cents' => 3999, 'discount_price_cents' => 3499, 'sort_order' => 30, 'metadata' => ['color' => '#7c63e8', 'features' => ['Sea Hunter kit', 'Extra commands', 'Higher limits']]],
        ['slug' => 'sea-beast', 'category' => 'ranks', 'name' => 'Sea Beast', 'description' => 'High-tier rank with powerful quality-of-life perks.', 'image' => 'assets/sea beast.png', 'price_cents' => 5499, 'discount_price_cents' => null, 'sort_order' => 40, 'metadata' => ['color' => '#d15a9f', 'features' => ['Sea Beast kit', 'Premium limits', 'Priority support']]],
        ['slug' => 'kraken', 'category' => 'ranks', 'name' => 'Kraken', 'description' => 'Top Atlantic rank with the complete perk set.', 'image' => 'assets/kraken.png', 'price_cents' => 7999, 'discount_price_cents' => null, 'sort_order' => 50, 'metadata' => ['color' => '#ef6c3e', 'badge' => 'Top rank', 'features' => ['Kraken kit', 'Maximum limits', 'Highest priority']]],
        ['slug' => 'rubis-1000', 'category' => 'rubis', 'name' => '1 000 Rubis', 'description' => 'A small Rubis pack for the Atlantic server.', 'image' => 'assets/rubis-saco-pequeno.png.png', 'price_cents' => 299, 'discount_price_cents' => null, 'sort_order' => 10, 'metadata' => ['amount' => '1 000']],
        ['slug' => 'rubis-5000', 'category' => 'rubis', 'name' => '5 000 Rubis', 'description' => 'A medium Rubis barrel with improved value.', 'image' => 'assets/rubis-barril.png.png', 'price_cents' => 999, 'discount_price_cents' => 799, 'sort_order' => 20, 'metadata' => ['amount' => '5 000', 'badge' => 'Popular']],
        ['slug' => 'rubis-15000', 'category' => 'rubis', 'name' => '15 000 Rubis', 'description' => 'A large Rubis chest for frequent players.', 'image' => 'assets/rubis-bau-grande.png.png', 'price_cents' => 2499, 'discount_price_cents' => null, 'sort_order' => 30, 'metadata' => ['amount' => '15 000', 'badge' => 'Best value']],
        ['slug' => 'dev-key', 'category' => 'keys', 'name' => 'Dev Key', 'description' => 'Open a Dev crate on the Atlantic server.', 'image' => 'assets/dev-key.png', 'price_cents' => 499, 'discount_price_cents' => null, 'sort_order' => 10, 'metadata' => ['theme' => 'dev']],
        ['slug' => 'atlantic-key', 'category' => 'keys', 'name' => 'Atlantic Key', 'description' => 'Open an Atlantic crate with valuable rewards.', 'image' => 'assets/atlantic-key.png', 'price_cents' => 799, 'discount_price_cents' => 649, 'sort_order' => 20, 'metadata' => ['theme' => 'atlantic', 'badge' => 'Popular']],
        ['slug' => 'magma-key', 'category' => 'keys', 'name' => 'Magma Key', 'description' => 'Open the premium Magma crate.', 'image' => 'assets/magma-key.png', 'price_cents' => 1499, 'discount_price_cents' => null, 'sort_order' => 30, 'metadata' => ['theme' => 'magma', 'badge' => 'Premium']],
        ['slug' => 'hearts-5', 'category' => 'boosters', 'name' => '5 Extra Hearts', 'description' => 'Five extra-life hearts for your adventures.', 'image' => 'assets/heart (2).png', 'price_cents' => 299, 'discount_price_cents' => null, 'sort_order' => 10, 'metadata' => ['amount' => '5', 'color' => '#ff5577']],
        ['slug' => 'hearts-10', 'category' => 'boosters', 'name' => '10 Extra Hearts', 'description' => 'Ten extra-life hearts with better value.', 'image' => 'assets/heart (2).png', 'price_cents' => 499, 'discount_price_cents' => null, 'sort_order' => 20, 'metadata' => ['amount' => '10', 'color' => '#ff4369', 'badge' => 'Popular']],
        ['slug' => 'hearts-25', 'category' => 'boosters', 'name' => '25 Extra Hearts', 'description' => 'Large heart bundle for long sessions.', 'image' => 'assets/heart (2).png', 'price_cents' => 999, 'discount_price_cents' => 849, 'sort_order' => 30, 'metadata' => ['amount' => '25', 'color' => '#e92f59', 'badge' => 'Best value']],
    ];

    $selectProduct = $pdo->prepare('SELECT id FROM products WHERE slug = :slug');
    $insertProduct = $pdo->prepare(
        'INSERT INTO products (slug, category, category_id, name, description, image, price_cents, discount_price_cents, currency, active, sort_order, tebex_package_id, metadata, created_at, updated_at)
         VALUES (:slug, :category, :category_id, :name, :description, :image, :price_cents, :discount_price_cents, :currency, 1, :sort_order, NULL, :metadata, :created_at, :updated_at)'
    );

    foreach ($products as $product) {
        $selectProduct->execute(['slug' => $product['slug']]);

        if ($selectProduct->fetchColumn() !== false) {
            continue;
        }

        $selectCategory->execute(['slug' => $product['category']]);
        $categoryId = $selectCategory->fetchColumn();

        if ($categoryId === false) {
            throw new RuntimeException('Missing seed category: ' . $product['category']);
        }

        $insertProduct->execute([
            'slug' => $product['slug'],
            'category' => $product['category'],
            'category_id' => (int) $categoryId,
            'name' => $product['name'],
            'description' => $product['description'],
            'image' => $product['image'],
            'price_cents' => $product['price_cents'],
            'discount_price_cents' => $product['discount_price_cents'],
            'currency' => config('app.currency', 'EUR'),
            'sort_order' => $product['sort_order'],
            'metadata' => json_encode($product['metadata'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    $deactivateTestCoupons = $pdo->prepare(
        'UPDATE coupons
         SET active = 0,
             updated_at = :updated_at
         WHERE code IN (:welcome_code, :atlantic_code)'
    );
    $deactivateTestCoupons->execute([
        'updated_at' => $now,
        'welcome_code' => 'WELCOME10',
        'atlantic_code' => 'ATLANTIC5',
    ]);

    if ((string) config('database.driver') === 'mysql') {
        $saveVersion = $pdo->prepare(
            'INSERT INTO app_meta (meta_key, meta_value) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)'
        );
    } else {
        $saveVersion = $pdo->prepare(
            'INSERT INTO app_meta (meta_key, meta_value) VALUES (:key, :value)
             ON CONFLICT(meta_key) DO UPDATE SET meta_value = excluded.meta_value'
        );
    }

    $saveVersion->execute(['key' => 'seed_version', 'value' => $seedVersion]);
}
