<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/app/Core/Config/config.php';

$errors = configuration_errors();

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, '- ' . $error . PHP_EOL);
    }

    exit(1);
}

fwrite(STDOUT, 'Production configuration is valid.' . PHP_EOL);
exit(0);
