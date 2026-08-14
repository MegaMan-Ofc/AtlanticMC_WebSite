<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $placementColumn = $pdo->query("SHOW COLUMNS FROM categories LIKE 'home_placement'")->fetch();

    if ($placementColumn === false) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN home_placement VARCHAR(16) NOT NULL DEFAULT 'grid' AFTER sort_order");
    }

    $sortColumn = $pdo->query("SHOW COLUMNS FROM categories LIKE 'home_sort_order'")->fetch();

    if ($sortColumn === false) {
        $pdo->exec('ALTER TABLE categories ADD COLUMN home_sort_order INT NOT NULL DEFAULT 0 AFTER home_placement');
    }

    $pdo->exec('UPDATE categories SET home_sort_order = sort_order WHERE home_sort_order = 0');

    $keysStatement = $pdo->query("SELECT id FROM categories WHERE slug = 'keys' LIMIT 1");
    $keysId = $keysStatement->fetchColumn();
    $topCount = (int) $pdo->query("SELECT COUNT(*) FROM categories WHERE home_placement = 'top'")->fetchColumn();

    if ($keysId !== false && $topCount === 0) {
        $statement = $pdo->prepare("UPDATE categories SET home_placement = 'top' WHERE id = :id");
        $statement->execute(['id' => (int) $keysId]);
    }

    $index = $pdo->query("SHOW INDEX FROM categories WHERE Key_name = 'idx_categories_home_layout'")->fetch();

    if ($index === false) {
        $pdo->exec('CREATE INDEX idx_categories_home_layout ON categories(home_placement, home_sort_order, id)');
    }
};
