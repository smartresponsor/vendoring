<?php

declare(strict_types=1);

require_once __DIR__ . '/_composer_json.php';

$root = dirname(__DIR__, 2) . '/src';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

foreach (vendoring_php_files($iterator) as $file) {
    $path = $file->getPathname();
    $contents = (string) file_get_contents($path);
    if (str_contains(strtolower($contents), 'stub')) {
        fwrite(STDERR, "Stub marker found in source: {$path}\n");
        exit(1);
    }
}

echo "stub source scan passed\n";
