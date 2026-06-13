<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$routeRoot = $root.'/config/platform/routes';

if (!is_dir($routeRoot)) {
    fwrite(STDERR, "Missing config/platform/routes directory.\n");
    exit(1);
}

$failures = [];
$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($routeRoot, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $fileInfo) {
    if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile() || 'yaml' !== $fileInfo->getExtension()) {
        continue;
    }
    $files[] = $fileInfo->getPathname();
}

sort($files);

if ([] === $files) {
    $failures[] = 'No leaf route-map YAML files found under config/platform/routes.';
}

$routeNames = [];
$serviceCount = 0;
$typeCount = 0;

foreach ($files as $file) {
    $contents = (string) file_get_contents($file);
    $relative = substr($file, strlen($root) + 1);

    foreach (preg_split('/\R/', $contents) ?: [] as $lineNo => $line) {
        $trimmed = trim($line);
        if ('' === $trimmed || str_starts_with($trimmed, '#')) {
            continue;
        }
        if (!str_contains($trimmed, ':')) {
            continue;
        }

        [$routeName] = explode(':', $trimmed, 2);
        $routeName = trim($routeName);
        if ('' === $routeName || 'imports' === $routeName) {
            continue;
        }
        if (isset($routeNames[$routeName])) {
            $failures[] = sprintf('Duplicate route-map key %s in %s and %s', $routeName, $routeNames[$routeName], $relative);
        }
        $routeNames[$routeName] = $relative;

        if (1 === preg_match('/service:\s*([^,}\s]+)/', $trimmed, $match)) {
            ++$serviceCount;
            $fqcn = $match[1];
            $path = $root.'/'.str_replace('\\', '/', preg_replace('/^App\\\\/', 'src/', $fqcn)).'.php';
            if (!is_file($path)) {
                $failures[] = sprintf('Missing service %s for %s:%d', $fqcn, $relative, $lineNo + 1);
            }
        }

        if (1 === preg_match('/type:\s*([^,}\s]+)/', $trimmed, $match)) {
            ++$typeCount;
            $fqcn = $match[1];
            $path = $root.'/'.str_replace('\\', '/', preg_replace('/^App\\\\/', 'src/', $fqcn)).'.php';
            if (!is_file($path)) {
                $failures[] = sprintf('Missing form type %s for %s:%d', $fqcn, $relative, $lineNo + 1);
            }
        }
    }
}

foreach (['config/platform/routes.crud.yaml', 'config/platform/routes.business.yaml', 'config/platform/routes.platform.yaml'] as $aggregate) {
    if (!is_file($root.'/'.$aggregate)) {
        $failures[] = 'Missing aggregate registry file: '.$aggregate;
    }
}

if ([] !== $failures) {
    fwrite(STDERR, "Vendor route-map coverage smoke failed:\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, ' - '.$failure.PHP_EOL);
    }
    exit(1);
}

echo sprintf(
    "Vendor route-map coverage smoke passed: %d route keys, %d services, %d types.\n",
    count($routeNames),
    $serviceCount,
    $typeCount
);
