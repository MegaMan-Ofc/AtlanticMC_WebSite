<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);
$phpFiles = [];
$jsFiles = [];

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile()) {
        continue;
    }

    $path = $file->getPathname();
    $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));

    if (str_starts_with($relative, '.git/') || str_starts_with($relative, 'storage/')) {
        continue;
    }

    if ($file->getExtension() === 'php') {
        $phpFiles[] = $path;
    } elseif ($file->getExtension() === 'js') {
        $jsFiles[] = $path;
    }
}

sort($phpFiles);
sort($jsFiles);
$failed = false;

foreach ($phpFiles as $file) {
    $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file);
    exec($command, $output, $status);

    if ($status !== 0) {
        $failed = true;
        fwrite(STDERR, implode(PHP_EOL, $output) . PHP_EOL);
    }

    $output = [];
}

exec('node --version', $nodeOutput, $nodeStatus);

if ($nodeStatus === 0) {
    foreach ($jsFiles as $file) {
        exec('node --check ' . escapeshellarg($file), $output, $status);

        if ($status !== 0) {
            $failed = true;
            fwrite(STDERR, implode(PHP_EOL, $output) . PHP_EOL);
        }

        $output = [];
    }
}

if ($failed) {
    exit(1);
}

fwrite(STDOUT, 'PHP files checked: ' . count($phpFiles) . PHP_EOL);
fwrite(STDOUT, 'JavaScript files checked: ' . ($nodeStatus === 0 ? count($jsFiles) : 0) . PHP_EOL);
exit(0);
