<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $columns = [
        'home_banner_kicker' => "VARCHAR(80) NULL AFTER home_sort_order",
        'home_banner_title' => "VARCHAR(120) NULL AFTER home_banner_kicker",
        'home_banner_text' => "VARCHAR(255) NULL AFTER home_banner_title",
        'home_banner_cta' => "VARCHAR(80) NULL AFTER home_banner_text",
        'home_banner_style' => "VARCHAR(20) NOT NULL DEFAULT 'auto' AFTER home_banner_cta",
        'home_banner_image_side' => "VARCHAR(10) NOT NULL DEFAULT 'right' AFTER home_banner_style",
        'home_banner_image_size' => "VARCHAR(10) NOT NULL DEFAULT 'normal' AFTER home_banner_image_side",
        'home_banner_show_watermark' => "TINYINT(1) NOT NULL DEFAULT 1 AFTER home_banner_image_size",
        'home_banner_show_cta' => "TINYINT(1) NOT NULL DEFAULT 1 AFTER home_banner_show_watermark",
    ];

    foreach ($columns as $name => $definition) {
        $column = $pdo->query("SHOW COLUMNS FROM categories LIKE " . $pdo->quote($name))->fetch();

        if ($column === false) {
            $pdo->exec("ALTER TABLE categories ADD COLUMN {$name} {$definition}");
        }
    }
};
