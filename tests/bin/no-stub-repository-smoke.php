<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$violations = [];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

/** @var SplFileInfo $file */
foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $path = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));

    foreach ([
        'report/',
        'tests/',
        'docs/',
        '.idea/',
        '.git',
        '.release/',
        'vendor/',
        '.deploy/',
        '.consuming/',
        'tools/report/VendorConfigGuardReport.php',
        '.php-cs-fixer.cache',
        'var/.php-cs-fixer.cache',
        'composer.json',
        'composer.lock',
    ] as $prefix) {
        if (str_starts_with($path, $prefix)) {
            continue 2;
        }
    }

    $content = (string) file_get_contents($file->getPathname());
    if (1 === preg_match('/\bstubs?\b/i', $content)) {
        $violations[] = $path;
    }
}

if ([] !== $violations) {
    fwrite(STDERR, 'Forbidden repository stub markers found: ' . implode(', ', $violations) . PHP_EOL);
    exit(1);
}

echo "OK\n";
