<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = t('faq.page_title');
$pageDescription = t('faq.description');
$bodyClass = 'page-faq';
$pageStyles = ['css/pages/faq.css'];

$faqItems = translation_value('faq.items');
$faqItems = is_array($faqItems) ? $faqItems : [];

$replacements = [
    'store' => (string) config('app.name'),
    'java_ip' => (string) config('app.server_ip'),
    'bedrock_ip' => (string) config('app.bedrock_server_ip'),
    'bedrock_port' => (string) config('app.bedrock_server_port'),
];

$faqItems = array_values(array_filter(array_map(
    static function (mixed $item) use ($replacements): ?array {
        if (!is_array($item)) {
            return null;
        }

        $question = trim((string) ($item['question'] ?? ''));
        $answer = trim((string) ($item['answer'] ?? ''));

        if ($question === '' || $answer === '') {
            return null;
        }

        foreach ($replacements as $name => $replacement) {
            $question = str_replace(':' . $name, $replacement, $question);
            $answer = str_replace(':' . $name, $replacement, $answer);
        }

        return [
            'question' => $question,
            'answer' => $answer,
        ];
    },
    $faqItems
)));
