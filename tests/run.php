<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

foreach ([
    'unit/CoreTest.php',
    'unit/TebexTest.php',
    'unit/RoutingTest.php',
    'unit/MediaTest.php',
    'unit/ProductDiscountTest.php',
    'unit/RecommendedProductsTest.php',
    'unit/HomeCategoryLayoutTest.php',
    'unit/HomeBannerCustomizationTest.php',
    'unit/AboutSectionTest.php',
    'unit/FaqTest.php',
    'unit/NotFoundPageTest.php',
    'unit/MaintenanceModeTest.php',
    'unit/ProductSearchTest.php',
    'unit/CartAjaxTest.php',
    'unit/AdminAnalyticsTest.php',
    'unit/SiteHardeningTest.php',
    'unit/AdminFiltersTest.php',
    'unit/FrontendTest.php',
    'integration/DatabaseTest.php',
] as $testFile) {
    require __DIR__ . '/' . $testFile;
}

@unlink($databasePath);
@unlink($maintenanceStatePath);
@unlink($appLogPath);
@unlink($securityLogPath);

exit($suite->finish());
