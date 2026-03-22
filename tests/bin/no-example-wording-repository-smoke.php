<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$files = [
    '.deploy/systemd/MANIFEST.md',
    '.commanding/systemd/MANIFEST.md',
    '.github/workflows/consuming.yml',
    '.consuming/.github/workflows/consuming.yml',
    'tools/vendoring-missing-class-scan-v2.php',
];
$hits = [];
foreach ($files as $file) {
    $absolutePath = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (!is_file($absolutePath)) {
        continue;
    }

    $contents = file_get_contents($absolutePath);
    if (false === $contents) {
        fwrite(STDERR, "Cannot read file: {$file}\n");
        exit(1);
    }

    if (str_contains($contents, 'example only') || str_contains($contents, 'example: canonization') || str_contains($contents, 'Examples:')) {
        $hits[] = $file;
    }
}
if ([] !== $hits) {
    fwrite(STDERR, 'Found repository-level example wording markers: '.implode(', ', $hits)."\n");
    exit(1);
}
$composerJson = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$scripts = $composerJson['scripts'] ?? [];
if (!array_key_exists('test:no-example-wording-repository', $scripts)) {
    fwrite(STDERR, "Missing composer script: test:no-example-wording-repository\n");
    exit(1);
}
exit(0);
