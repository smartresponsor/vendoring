<?php

declare(strict_types=1);

require_once __DIR__ . '/_composer_json.php';

$root = dirname(__DIR__, 2);
$paths = ['.deploy', 'ops', 'config', 'scripts', '.smoke', 'bin', 'public', 'tools', 'src'];
$hits = [];
$allowedPrefixes = ['.deploy/_template/', '.deploy/systemd/', '.consuming/', 'vendor/'];
foreach ($paths as $path) {
    $absolutePath = $root . DIRECTORY_SEPARATOR . $path;
    if (!is_dir($absolutePath)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolutePath, FilesystemIterator::SKIP_DOTS));
    foreach (vendoring_php_files($iterator) as $file) {
        $relative = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($relative, $prefix)) {
                continue 2;
            }
        }
        $contents = (string) file_get_contents($file->getPathname());
        if (str_contains(strtolower($contents), 'example')) {
            $hits[] = $relative;
        }
    }
}
if ([] !== $hits) {
    fwrite(STDERR, 'Found example wording markers in repository: ' . implode(', ', $hits) . PHP_EOL);
    exit(1);
}
$composer = vendoring_load_composer_json($root);
if (!vendoring_has_script($composer, 'test:no-example-wording-repository')) {
    fwrite(STDERR, "Missing composer script test:no-example-wording-repository\n");
    exit(1);
}
echo "example wording repository smoke passed\n";
