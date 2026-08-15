<?php

declare(strict_types=1);

$faqPage = file_get_contents($root . '/public_html/faq.php');
$faqController = file_get_contents($root . '/controllers/Site/faq.php');
$faqStyles = file_get_contents($root . '/public_html/css/pages/faq.css');
$footerTemplate = file_get_contents($root . '/templates/layout/footer.php');
$legalTemplate = file_get_contents($root . '/templates/site/legal.php');
$homePage = file_get_contents($root . '/public_html/index.php');
$publicHtaccess = file_get_contents($root . '/public_html/.htaccess');

$assert(
    is_string($faqPage)
        && str_contains($faqPage, '<details class="faq-item">')
        && str_contains($faqPage, '<summary>')
        && str_contains($faqPage, 'faq-answer')
        && str_contains($faqPage, 'legal-navigation')
        && str_contains($faqPage, 'aria-current="page"')
        && strpos($faqPage, "route_url('faq')") < strpos($faqPage, 'livroreclamacoes.pt'),
    'The FAQ page uses native expandable questions and includes the legal navigation with FAQ selected.'
);

$assert(
    is_string($faqController)
        && str_contains($faqController, "translation_value('faq.items')")
        && str_contains($faqController, "'css/pages/legal.css'")
        && str_contains($faqController, "'css/pages/faq.css'")
        && str_contains($faqController, 'legal_navigation()'),
    'The FAQ controller loads localized questions and the shared legal navigation styles.'
);

$assert(
    is_string($faqStyles)
        && str_contains($faqStyles, '.faq-item[open]')
        && str_contains($faqStyles, '.faq-answer')
        && str_contains($faqStyles, 'padding: .55rem 1.35rem 1.3rem;'),
    'FAQ styles include the expanded state and breathing room above each answer.'
);

$assert(
    is_string($footerTemplate)
        && str_contains($footerTemplate, "route_url('faq')")
        && !str_contains($footerTemplate, "mailto:<?= e(config('app.support_email')) ?>"),
    'The footer replaces the email action with the FAQ action.'
);

$assert(
    is_string($legalTemplate)
        && str_contains($legalTemplate, "route_url('faq')")
        && strpos($legalTemplate, "route_url('faq')") < strpos($legalTemplate, 'livroreclamacoes.pt'),
    'Legal navigation shows FAQ before the Book of Complaints.'
);

$assert(
    is_string($homePage)
        && str_contains($homePage, "home.about_faq_prompt")
        && str_contains($homePage, "route_url('faq')")
        && strpos($homePage, "home.about_tag_community") < strpos($homePage, "home.about_faq_prompt"),
    'The first About section shows the community highlight before the FAQ prompt.'
);

$assert(
    is_string($publicHtaccess)
        && str_contains($publicHtaccess, 'purchase-policy|rules|faq|admin'),
    'Clean FAQ routing is enabled in the public Apache rewrite rules.'
);

$portugueseFaqItems = translation_value('faq.items', 'pt');
$englishFaqItems = translation_value('faq.items', 'en');
$assert(
    is_array($portugueseFaqItems)
        && is_array($englishFaqItems)
        && count($portugueseFaqItems) >= 10
        && count($portugueseFaqItems) === count($englishFaqItems),
    'FAQ content is available in Portuguese and English with matching question counts.'
);
