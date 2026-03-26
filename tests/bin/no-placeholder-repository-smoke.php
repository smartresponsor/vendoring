<?php

declare(strict_types=1);

require_once __DIR__.'/_composer_json.php';

$root = dirname(__DIR__, 2);
$skipPrefixes = [
    $root.'/report/',
    $root.'/tests/',
    $root.'/.idea/',
    $root.'/.phpunit.cache/',
    $root.'/build/docs/phpdocumentor/',
    $root.'/docs/release/',
    $root.'/var/',
    $root.'/.deploy/_template/',
];
$skipFiles = [
    $root.'/composer.json',
    $root.'/bin/generate-phpdocumentor-site.php',
    $root.'/bin/generate-rc-evidence.php',
    $root.'/config/reference.php',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile()) {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());
    if (
        str_contains($path, '/.git/')
        || str_contains($path, '/vendor/')
        || str_contains($path, '/.deploy/_template/')
    ) {
        continue;
    }

    if (in_array($path, array_map(static fn (string $item): string => str_replace('\\', '/', $item), $skipFiles), true)) {
        continue;
    }

    $skip = false;
    foreach ($skipPrefixes as $prefix) {
        if (str_starts_with($path, str_replace('\\', '/', $prefix))) {
            $skip = true;
            break;
        }
    }
    if ($skip) {
        continue;
    }

    $contents = file_get_contents($path);
    if (false === $contents) {
        fwrite(STDERR, 'Unable to read: '.$path.PHP_EOL);
        exit(1);
    }

    if (false !== stripos($contents, 'placeholder')) {
        fwrite(STDERR, 'Forbidden repository placeholder marker remains in: '.$path.PHP_EOL);
        exit(1);
    }
}

echo "no-placeholder-repository smoke passed\n";
