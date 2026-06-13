<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$serviceRoot = $root.'/src/Service/Http/Vendor';

$errors = [];

if (!is_dir($serviceRoot)) {
    fwrite(STDERR, "Missing src/Service/Http/Vendor.\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($serviceRoot, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile() || 'php' !== $file->getExtension()) {
        continue;
    }

    $path = $file->getPathname();
    $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));
    $contents = (string) file_get_contents($path);

    if (!str_contains($contents, 'namespace App\\Vendoring\\Service\\Http\\Vendor')) {
        $errors[] = sprintf('%s must stay under App\\Vendoring\\Service\\Http\\Vendor namespace', $relative);
    }

    if (str_contains($contents, 'extends AbstractController') || str_contains($contents, '#[Route(')) {
        $errors[] = sprintf('%s must not contain controller/route vocabulary', $relative);
    }
}

if ([] !== $errors) {
    fwrite(STDERR, "Vendor HTTP support service smoke failed:\n");
    foreach ($errors as $error) {
        fwrite(STDERR, ' - '.$error."\n");
    }
    exit(1);
}

echo "Vendor HTTP support service smoke passed.\n";
