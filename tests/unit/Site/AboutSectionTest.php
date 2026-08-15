<?php

declare(strict_types=1);

$homeTemplate = file_get_contents($root . '/public_html/index.php');
$homeStyles = file_get_contents($root . '/public_html/css/pages/home.css');
$footerTemplate = file_get_contents($root . '/templates/layout/footer.php');

$assert(
    is_string($homeTemplate)
        && str_contains($homeTemplate, 'home-about-grid')
        && str_contains($homeTemplate, "home.about_community_title")
        && str_contains($homeTemplate, "home.about_adventure_title")
        && substr_count($homeTemplate, 'home-section-divider') >= 2
        && is_string($homeStyles)
        && str_contains($homeStyles, '"image-one text-one"')
        && str_contains($homeStyles, '"text-two image-two"')
        && str_contains($homeStyles, '"text-one"')
        && str_contains($homeStyles, '"image-one"')
        && is_string($footerTemplate)
        && !str_contains($footerTemplate, "footer.about_title")
        && !str_contains($footerTemplate, 'footer-about'),
    'The homepage About section uses the checkerboard desktop layout, stacked mobile order, reusable section dividers, and replaces the old footer About block.'
);
