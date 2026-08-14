<?php

declare(strict_types=1);

$faqPage = file_get_contents($root . '/public_html/faq.php');
$faqController = file_get_contents($root . '/controllers/faq.php');
$faqStyles = file_get_contents($root . '/public_html/css/pages/faq.css');
$footerTemplate = file_get_contents($root . '/includes/footer.php');
$legalTemplate = file_get_contents($root . '/templates/legal.php');
$homePage = file_get_contents($root . '/public_html/index.php');
$publicHtaccess = file_get_contents($root . '/public_html/.htaccess');

$assert(
    is_string($faqPage)
        && str_contains($faqPage, '<details class="faq-item">')
        && str_contains($faqPage, '<summary>')
        && str_contains($faqPage, 'faq-answer'),
    'The FAQ page uses native expandable questions that remain open until toggled again.'
);

$assert(
    is_string($faqController)
        && str_contains($faqController, "translation_value('faq.items')")
        && str_contains($faqController, "'css/pages/faq.css'"),
    'The FAQ controller loads localized questions and dedicated styles.'
);

$assert(
    is_string($faqStyles)
        && str_contains($faqStyles, '.faq-item[open]')
        && str_contains($faqStyles, '.faq-answer'),
    'FAQ styles include the expanded state and answer layout.'
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
        && str_contains($homePage, "route_url('faq')"),
    'The first About section copy links visitors to the FAQ.'
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
