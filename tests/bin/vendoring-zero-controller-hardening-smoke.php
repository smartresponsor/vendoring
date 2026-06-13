<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$errors = [];

$forbiddenPhysicalPaths = [
    $root.'/src/Controller',
    $root.'/src/ControllerTrait',
    $root.'/config/vendor_routes.yaml',
];

foreach ($forbiddenPhysicalPaths as $path) {
    if (file_exists($path)) {
        $errors[] = sprintf('Forbidden physical path exists: %s', substr($path, strlen($root) + 1));
    }
}

$forbiddenNeedles = [
    'App\\Vendoring\\Controller\\' => 'App\\Vendoring\\Controller namespace',
    'Controller\\Vendor' => 'Controller\\Vendor namespace',
    'Symfony\\Component\\Routing\\Annotation\\Route' => 'Routing Annotation Route import',
    'Symfony\\Component\\Routing\\Attribute\\Route' => 'Routing Attribute Route import',
    '#[Route(' => '#[Route] attribute',
    'extends AbstractController' => 'extends AbstractController',
];

$pathNeedles = [
    'src/Controller' => 'src/Controller reference',
    'src\\Controller' => 'src\\Controller reference',
    'vendor_routes.yaml' => 'vendor_routes.yaml reference',
];

// Do not scan tests/bin: smoke scripts intentionally contain forbidden strings as negative checks.
$scanRoots = [
    'composer.json',
    'config',
    'src',
    'tests/Unit',
    'tools/report',
];

foreach ($scanRoots as $relativeScanRoot) {
    $scanRoot = $root.'/'.$relativeScanRoot;

    if (is_file($scanRoot)) {
        $files = [new SplFileInfo($scanRoot)];
    } elseif (is_dir($scanRoot)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS)
        );
    } else {
        continue;
    }

    foreach ($files as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $path = $file->getPathname();
        $relativePath = str_replace('\\', '/', substr($path, strlen($root) + 1));

        if (!str_ends_with($path, '.php')
            && !str_ends_with($path, '.yaml')
            && !str_ends_with($path, '.yml')
            && 'composer.json' !== basename($path)
        ) {
            continue;
        }

        $contents = (string) file_get_contents($path);

        foreach ($forbiddenNeedles as $needle => $label) {
            if (str_contains($contents, $needle)) {
                $errors[] = sprintf('%s contains forbidden %s', $relativePath, $label);
            }
        }

        foreach ($pathNeedles as $needle => $label) {
            if (str_contains($contents, $needle)) {
                $errors[] = sprintf('%s contains forbidden %s', $relativePath, $label);
            }
        }
    }
}

if ([] !== $errors) {
    fwrite(STDERR, "Vendoring zero-controller hardening smoke failed:\n");
    foreach ($errors as $error) {
        fwrite(STDERR, ' - '.$error."\n");
    }
    exit(1);
}

echo "Vendoring zero-controller hardening smoke passed.\n";
