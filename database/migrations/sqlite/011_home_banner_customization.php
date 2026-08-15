<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $columns = $pdo->query('PRAGMA table_info(categories)')->fetchAll();
    $existing = [];

    foreach ($columns as $column) {
        $existing[(string) ($column['name'] ?? '')] = true;
    }

    $definitions = [
        'home_banner_kicker' => 'TEXT NULL',
        'home_banner_title' => 'TEXT NULL',
        'home_banner_text' => 'TEXT NULL',
        'home_banner_cta' => 'TEXT NULL',
        'home_banner_style' => "TEXT NOT NULL DEFAULT 'auto'",
        'home_banner_image_side' => "TEXT NOT NULL DEFAULT 'right'",
        'home_banner_image_size' => "TEXT NOT NULL DEFAULT 'normal'",
        'home_banner_show_watermark' => 'INTEGER NOT NULL DEFAULT 1',
        'home_banner_show_cta' => 'INTEGER NOT NULL DEFAULT 1',
    ];

    foreach ($definitions as $name => $definition) {
        if (!isset($existing[$name])) {
            $pdo->exec("ALTER TABLE categories ADD COLUMN {$name} {$definition}");
        }
    }
};
