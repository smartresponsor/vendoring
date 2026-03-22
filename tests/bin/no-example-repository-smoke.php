<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$paths = [
    '.commanding',
    '.deploy',
    'ops',
    'config',
    'scripts',
    '.smoke',
    'bin',
    'public',
    'tools',
    'src',
];
$hits = [];
foreach ($paths as $path) {
    $absolutePath = $root.DIRECTORY_SEPARATOR.$path;
    if (!is_dir($absolutePath)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absolutePath, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $contents = file_get_contents($file->getPathname());
        if (false === $contents) {
            fwrite(STDERR, "Cannot read file: {$file->getPathname()}
");
            exit(1);
        }
        if (str_contains($contents, 'example.com')) {
            $hits[] = str_replace($root.DIRECTORY_SEPARATOR, '', $file->getPathname());
        }
    }
}
if ([] !== $hits) {
    fwrite(STDERR, 'Found example.com markers in operational repository layers: '.implode(', ', $hits).'
');
    exit(1);
}
$composerJson = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$scripts = $composerJson['scripts'] ?? [];
if (!array_key_exists('test:no-example-repository', $scripts)) {
    fwrite(STDERR, 'Missing composer script: test:no-example-repository
');
    exit(1);
}
exit(0);
