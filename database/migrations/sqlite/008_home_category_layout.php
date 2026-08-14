<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $columns = array_column($pdo->query('PRAGMA table_info(categories)')->fetchAll(), 'name');

    if (!in_array('home_placement', $columns, true)) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN home_placement TEXT NOT NULL DEFAULT 'grid'");
    }

    if (!in_array('home_sort_order', $columns, true)) {
        $pdo->exec('ALTER TABLE categories ADD COLUMN home_sort_order INTEGER NOT NULL DEFAULT 0');
    }

    $pdo->exec('UPDATE categories SET home_sort_order = sort_order WHERE home_sort_order = 0');

    $keysId = $pdo->query("SELECT id FROM categories WHERE slug = 'keys' LIMIT 1")->fetchColumn();
    $topCount = (int) $pdo->query("SELECT COUNT(*) FROM categories WHERE home_placement = 'top'")->fetchColumn();

    if ($keysId !== false && $topCount === 0) {
        $statement = $pdo->prepare("UPDATE categories SET home_placement = 'top' WHERE id = :id");
        $statement->execute(['id' => (int) $keysId]);
    }

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_categories_home_layout ON categories(home_placement, home_sort_order, id)');
};
