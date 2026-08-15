<?php

declare(strict_types=1);

$adminAuthSource = file_get_contents($root . '/app/Admin/Auth/auth.php');
$databaseCheckSource = file_get_contents($root . '/bin/check-database.php');
$composeSource = file_get_contents($root . '/compose.yaml');
$productCardSource = file_get_contents($root . '/templates/components/product-card.php');
$mainJavaScript = file_get_contents($root . '/public_html/js/main.js');
$privacyPt = file_get_contents($root . '/translations/pt.php');
$privacyEn = file_get_contents($root . '/translations/en.php');

$assert(
    is_string($adminAuthSource)
        && !str_contains($adminAuthSource, 'session_regenerate_id(false)')
        && substr_count($adminAuthSource, 'session_regenerate_id(true)') >= 3,
    'Admin session rotation invalidates obsolete session identifiers.'
);

$assert(
    is_string($databaseCheckSource)
        && str_contains($databaseCheckSource, 'migration_files($driver)')
        && str_contains($databaseCheckSource, 'applied_migrations($pdo)')
        && str_contains($databaseCheckSource, 'Missing migrations'),
    'Database health checks detect unapplied migration files.'
);

$assert(
    is_string($composeSource)
        && str_contains($composeSource, '127.0.0.1:3307:3306'),
    'The local Docker MySQL port is bound to localhost only.'
);

$assert(
    is_string($productCardSource)
        && str_contains($productCardSource, 'data-product-analytics')
        && str_contains($productCardSource, 'loading="lazy"')
        && str_contains($productCardSource, 'decoding="async"')
        && is_file($root . '/public_html/ajax/product-interaction.php')
        && is_string($mainJavaScript)
        && str_contains($mainJavaScript, 'trackProductCardInteraction'),
    'Product cards use deferred image decoding and privacy-friendly interaction tracking.'
);

$assert(
    is_string($privacyPt)
        && is_string($privacyEn)
        && !str_contains($privacyPt, 'Esta informação deve ser atualizada antes de adicionar serviços de estatística')
        && !str_contains($privacyEn, 'This information must be updated before adding analytics')
        && str_contains($privacyPt, 'Métricas agregadas de utilização')
        && str_contains($privacyEn, 'Aggregated usage metrics'),
    'Privacy text documents the aggregate first-party analytics now used by the store.'
);
