<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composerPath = $root.'/composer.json';
$composer = json_decode((string) file_get_contents($composerPath), true);

if (!is_array($composer)) {
    fwrite(STDERR, "Unable to parse composer.json\n");
    exit(1);
}

$psr4 = $composer['autoload']['psr-4'] ?? [];
if (!is_array($psr4)) {
    fwrite(STDERR, "composer.json autoload.psr-4 must be an object\n");
    exit(1);
}

if (['App\\' => 'src/'] !== $psr4) {
    fwrite(STDERR, "autoload.psr-4 must contain only App\\\\ => src/\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/src', FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile() || 'php' !== $file->getExtension()) {
        continue;
    }

    $source = (string) file_get_contents($file->getPathname());
    if (!preg_match('/^namespace\s+([^;]+);/m', $source, $matches)) {
        fwrite(STDERR, "Missing namespace in src file: {$file->getPathname()}\n");
        exit(1);
    }

    $namespace = trim($matches[1]);
    if (!str_starts_with($namespace, 'App\\') && 'App' !== $namespace) {
        fwrite(STDERR, "Non-App namespace detected in src/: {$namespace} ({$file->getPathname()})\n");
        exit(1);
    }

    $namespaceLower = strtolower($namespace);
    if (str_starts_with($namespaceLower, 'smartresponsor')) {
        fwrite(STDERR, "Forbidden Smartresponsor namespace detected: {$namespace}\n");
        exit(1);
    }
}

echo "app-namespace-repository-smoke: ok\n";
