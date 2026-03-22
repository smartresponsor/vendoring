<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$skipPrefixes = [
    $root.'/report/',
    $root.'/tests/',
    $root.'/.idea/',
];
$skipFiles = [
    $root.'/composer.json',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());
    if (str_contains($path, '/.git/') || str_contains($path, '/vendor/')) {
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
