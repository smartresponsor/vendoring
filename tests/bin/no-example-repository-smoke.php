<?php

declare(strict_types=1);

require_once __DIR__ . '/_composer_json.php';

$root = dirname(__DIR__, 2);
$paths = ['.commanding', 'deploy', 'ops', 'config', 'scripts', 'ops/policy/smoke', 'bin', 'public', 'tools', 'src'];
$hits = [];
foreach ($paths as $path) {
    $absolutePath = $root . DIRECTORY_SEPARATOR . $path;
    if (!is_dir($absolutePath)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolutePath, FilesystemIterator::SKIP_DOTS));
    foreach (vendoring_php_files($iterator) as $file) {
        $contents = file_get_contents($file->getPathname());
        if (false === $contents) {
            fwrite(STDERR, "Cannot read file: {$file->getPathname()}\n");
            exit(1);
        }
        if (str_contains($contents, 'example.com')) {
            $hits[] = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
        }
    }
}
if ([] !== $hits) {
    fwrite(STDERR, 'Found example.com markers in operational repository layers: ' . implode(', ', $hits) . PHP_EOL);
    exit(1);
}
$composer = vendoring_load_composer_json($root);
if (!vendoring_has_script($composer, 'test:no-example-repository')) {
    fwrite(STDERR, "Missing composer script test:no-example-repository\n");
    exit(1);
}
echo "example repository smoke passed\n";
