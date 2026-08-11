<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS categories (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(80) NOT NULL UNIQUE,
            name VARCHAR(80) NOT NULL,
            image VARCHAR(255) NOT NULL DEFAULT \'\',
            active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX idx_categories_active_sort (active, sort_order, id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $column = $pdo->query("SHOW COLUMNS FROM products LIKE 'category_id'")->fetch();

    if ($column === false) {
        $pdo->exec('ALTER TABLE products ADD COLUMN category_id BIGINT UNSIGNED NULL AFTER category');
    }

    $index = $pdo->query("SHOW INDEX FROM products WHERE Key_name = 'idx_products_category_id'")->fetch();

    if ($index === false) {
        $pdo->exec('CREATE INDEX idx_products_category_id ON products(category_id, active, sort_order)');
    }

    $defaults = [
        'ranks' => ['VIPs', 'assets/diamante.png', 10],
        'rubis' => ['Rubis', 'assets/rubis-saco-pequeno.png.png', 20],
        'keys' => ['Chaves', 'assets/atlantic-key.png', 30],
        'boosters' => ['Corações', 'assets/heart (2).png', 40],
    ];

    $meta = [];
    $metaStatement = $pdo->query(
        "SELECT meta_key, meta_value
         FROM app_meta
         WHERE meta_key LIKE 'store_category.%'"
    );

    foreach ($metaStatement->fetchAll() as $row) {
        $meta[(string) $row['meta_key']] = (string) $row['meta_value'];
    }

    $slugs = array_keys($defaults);

    $selectCategory = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug');
    $insertCategory = $pdo->prepare(
        'INSERT INTO categories (slug, name, image, active, sort_order, created_at, updated_at)
         VALUES (:slug, :name, :image, 1, :sort_order, :created_at, :updated_at)'
    );
    $updateProducts = $pdo->prepare(
        'UPDATE products SET category_id = :category_id WHERE category = :slug'
    );
    $now = date('Y-m-d H:i:s');

    foreach ($slugs as $index => $slug) {
        $default = $defaults[$slug] ?? [ucwords(str_replace(['-', '_'], ' ', $slug)), '', ($index + 1) * 10];
        $name = trim($meta['store_category.' . $slug . '.name'] ?? '') ?: $default[0];
        $image = trim($meta['store_category.' . $slug . '.image'] ?? '') ?: $default[1];

        $selectCategory->execute(['slug' => $slug]);
        $categoryId = $selectCategory->fetchColumn();

        if ($categoryId === false) {
            $insertCategory->execute([
                'slug' => $slug,
                'name' => $name,
                'image' => $image,
                'sort_order' => $default[2],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $categoryId = (int) $pdo->lastInsertId();
        }

        $updateProducts->execute([
            'category_id' => (int) $categoryId,
            'slug' => $slug,
        ]);
    }
};
