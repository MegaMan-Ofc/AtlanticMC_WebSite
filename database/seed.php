<?php

declare(strict_types=1);

function seed_store_database(PDO $pdo): void
{
    $seedVersion = '1';
    $versionStatement = $pdo->prepare('SELECT meta_value FROM app_meta WHERE meta_key = :key');
    $versionStatement->execute(['key' => 'seed_version']);

    if ($versionStatement->fetchColumn() === $seedVersion) {
        return;
    }

    $now = now_sql();
    $products = json_decode('[{"slug": "deep-diver", "category": "ranks", "name": "Deep Diver", "description": "Starter rank with useful commands and a starter kit.", "image": "assets/deep diver.png", "price_cents": 1499, "sort_order": 10, "tebex_package_id": null, "metadata": {"color": "#48b9d4", "features": ["Starter kit", "Priority queue", "Useful starter commands"]}}, {"slug": "sailor", "category": "ranks", "name": "Sailor", "description": "Expanded permissions and a stronger recurring kit.", "image": "assets/sailor.png", "price_cents": 2499, "sort_order": 20, "tebex_package_id": null, "metadata": {"color": "#4f8ee8", "features": ["Sailor kit", "More homes", "Queue priority"]}}, {"slug": "sea-hunter", "category": "ranks", "name": "Sea Hunter", "description": "Advanced rank for active Atlantic players.", "image": "assets/sea hunter.png", "price_cents": 3999, "sort_order": 30, "tebex_package_id": null, "metadata": {"color": "#7c63e8", "features": ["Sea Hunter kit", "Extra commands", "Higher limits"]}}, {"slug": "sea-beast", "category": "ranks", "name": "Sea Beast", "description": "High-tier rank with powerful quality-of-life perks.", "image": "assets/sea beast.png", "price_cents": 5499, "sort_order": 40, "tebex_package_id": null, "metadata": {"color": "#d15a9f", "features": ["Sea Beast kit", "Premium limits", "Priority support"]}}, {"slug": "kraken", "category": "ranks", "name": "Kraken", "description": "Top Atlantic rank with the complete perk set.", "image": "assets/kraken.png", "price_cents": 7999, "sort_order": 50, "tebex_package_id": null, "metadata": {"color": "#ef6c3e", "badge": "Top rank", "features": ["Kraken kit", "Maximum limits", "Highest priority"]}}, {"slug": "rubis-1000", "category": "rubis", "name": "1 000 Rubis", "description": "A small Rubis pack for the Atlantic server.", "image": "assets/rubis-saco-pequeno.png.png", "price_cents": 299, "sort_order": 10, "tebex_package_id": null, "metadata": {"amount": "1 000"}}, {"slug": "rubis-5000", "category": "rubis", "name": "5 000 Rubis", "description": "A medium Rubis barrel with improved value.", "image": "assets/rubis-barril.png.png", "price_cents": 999, "sort_order": 20, "tebex_package_id": null, "metadata": {"amount": "5 000", "badge": "Popular"}}, {"slug": "rubis-15000", "category": "rubis", "name": "15 000 Rubis", "description": "A large Rubis chest for frequent players.", "image": "assets/rubis-bau-grande.png.png", "price_cents": 2499, "sort_order": 30, "tebex_package_id": null, "metadata": {"amount": "15 000", "badge": "Best value"}}, {"slug": "dev-key", "category": "keys", "name": "Dev Key", "description": "Open a Dev crate on the Atlantic server.", "image": "assets/dev-key.png", "price_cents": 499, "sort_order": 10, "tebex_package_id": null, "metadata": {"theme": "dev"}}, {"slug": "atlantic-key", "category": "keys", "name": "Atlantic Key", "description": "Open an Atlantic crate with valuable rewards.", "image": "assets/atlantic-key.png", "price_cents": 799, "sort_order": 20, "tebex_package_id": null, "metadata": {"theme": "atlantic", "badge": "Popular"}}, {"slug": "magma-key", "category": "keys", "name": "Magma Key", "description": "Open the premium Magma crate.", "image": "assets/magma-key.png", "price_cents": 1499, "sort_order": 30, "tebex_package_id": null, "metadata": {"theme": "magma", "badge": "Premium"}}, {"slug": "battle-pass-basic", "category": "battlepass", "name": "Battle Pass", "description": "Unlock the premium Battle Pass reward path.", "image": "assets/diamante.png", "price_cents": 999, "sort_order": 10, "tebex_package_id": null, "metadata": {"features": ["Premium reward path", "Season rewards", "Progress bonuses"]}}, {"slug": "battle-pass-25", "category": "battlepass", "name": "Battle Pass +25 Levels", "description": "Unlock the Battle Pass and start 25 levels ahead.", "image": "assets/diamante.png", "price_cents": 1999, "sort_order": 20, "tebex_package_id": null, "metadata": {"badge": "Fast start", "features": ["Premium reward path", "25 levels included", "Season rewards"]}}, {"slug": "battle-pass-complete", "category": "battlepass", "name": "Complete Battle Pass", "description": "Unlock the complete seasonal package.", "image": "assets/diamante.png", "price_cents": 4999, "sort_order": 30, "tebex_package_id": null, "metadata": {"badge": "Complete", "features": ["Premium reward path", "Full level boost", "500 bonus Rubis"]}}, {"slug": "hearts-5", "category": "boosters", "name": "5 Extra Hearts", "description": "Five extra-life hearts for your adventures.", "image": "assets/heart (2).png", "price_cents": 299, "sort_order": 10, "tebex_package_id": null, "metadata": {"amount": "5", "color": "#ff5577"}}, {"slug": "hearts-10", "category": "boosters", "name": "10 Extra Hearts", "description": "Ten extra-life hearts with better value.", "image": "assets/heart (2).png", "price_cents": 499, "sort_order": 20, "tebex_package_id": null, "metadata": {"amount": "10", "color": "#ff4369", "badge": "Popular"}}, {"slug": "hearts-25", "category": "boosters", "name": "25 Extra Hearts", "description": "Large heart bundle for long sessions.", "image": "assets/heart (2).png", "price_cents": 999, "sort_order": 30, "tebex_package_id": null, "metadata": {"amount": "25", "color": "#e92f59", "badge": "Best value"}}]', true, 512, JSON_THROW_ON_ERROR);

    $selectProduct = $pdo->prepare('SELECT id FROM products WHERE slug = :slug');
    $insertProduct = $pdo->prepare(
        'INSERT INTO products (slug, category, name, description, image, price_cents, currency, active, sort_order, tebex_package_id, metadata, created_at, updated_at)
         VALUES (:slug, :category, :name, :description, :image, :price_cents, :currency, 1, :sort_order, :tebex_package_id, :metadata, :created_at, :updated_at)'
    );

    foreach ($products as $product) {
        $selectProduct->execute(['slug' => $product['slug']]);

        if ($selectProduct->fetchColumn() !== false) {
            continue;
        }

        $insertProduct->execute([
            'slug' => $product['slug'],
            'category' => $product['category'],
            'name' => $product['name'],
            'description' => $product['description'],
            'image' => $product['image'],
            'price_cents' => $product['price_cents'],
            'currency' => config('app.currency', 'EUR'),
            'sort_order' => $product['sort_order'],
            'tebex_package_id' => $product['tebex_package_id'],
            'metadata' => json_encode($product['metadata'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    $coupons = [
        ['code' => 'WELCOME10', 'discount_type' => 'percentage', 'discount_value' => 10, 'min_subtotal_cents' => 500, 'max_uses' => 100],
        ['code' => 'ATLANTIC5', 'discount_type' => 'fixed', 'discount_value' => 500, 'min_subtotal_cents' => 2500, 'max_uses' => 50],
    ];

    $selectCoupon = $pdo->prepare('SELECT id FROM coupons WHERE code = :code');
    $insertCoupon = $pdo->prepare(
        'INSERT INTO coupons (code, discount_type, discount_value, min_subtotal_cents, max_uses, used_count, active, expires_at, created_at, updated_at)
         VALUES (:code, :discount_type, :discount_value, :min_subtotal_cents, :max_uses, 0, 1, NULL, :created_at, :updated_at)'
    );

    foreach ($coupons as $coupon) {
        $selectCoupon->execute(['code' => $coupon['code']]);

        if ($selectCoupon->fetchColumn() !== false) {
            continue;
        }

        $insertCoupon->execute($coupon + ['created_at' => $now, 'updated_at' => $now]);
    }

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
