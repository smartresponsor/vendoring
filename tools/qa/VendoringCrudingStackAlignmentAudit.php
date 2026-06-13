<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];

/** @return list<string> */
function wave13PhpFiles(string $directory): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $files[] = $file->getPathname();
    }

    sort($files);

    return $files;
}

/** @return list<string> */
function wave13RouteMapFiles(string $routeRoot): array
{
    if (!is_dir($routeRoot)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($routeRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'yaml') {
            continue;
        }

        $files[] = $file->getPathname();
    }

    sort($files);

    return $files;
}

$routeFiles = wave13RouteMapFiles($root.'/config/platform/routes');
$serviceTargets = [];
$formTargets = [];

foreach ($routeFiles as $routeFile) {
    $source = file_get_contents($routeFile) ?: '';

    if (preg_match_all('/service:\s*([^,}\s]+)/', $source, $matches) > 0) {
        foreach ($matches[1] as $fqcn) {
            $serviceTargets[$fqcn] = true;
        }
    }

    if (preg_match_all('/type:\s*([^,}\s]+)/', $source, $matches) > 0) {
        foreach ($matches[1] as $fqcn) {
            $formTargets[$fqcn] = true;
        }
    }
}

foreach (array_keys($serviceTargets) as $fqcn) {
    $path = $root.'/'.str_replace('\\', '/', preg_replace('/^App\\\\/', 'src/', $fqcn)).'.php';
    if (!is_file($path)) {
        $errors[] = 'Missing HTTP service target: '.$fqcn;
    }
}

foreach (array_keys($formTargets) as $fqcn) {
    $path = $root.'/'.str_replace('\\', '/', preg_replace('/^App\\\\/', 'src/', $fqcn)).'.php';
    if (!is_file($path)) {
        $errors[] = 'Missing form type target: '.$fqcn;
    }
}

$responseService = $root.'/src/Service/Http/Vendor/VendorHttpRouteResponseService.php';
if (!is_file($responseService)) {
    $errors[] = 'Missing VendorHttpRouteResponseService.php';
} else {
    $source = file_get_contents($responseService) ?: '';
    foreach ([
        'CrudSurfaceContract',
        'crudingContract',
        'viewingContract',
        'interfacingDependency',
        'not-bound-inside-vendoring',
        'read_route_ready',
        'route_blocked',
    ] as $needle) {
        if (!str_contains($source, $needle)) {
            $errors[] = 'VendorHttpRouteResponseService missing marker: '.$needle;
        }
    }
}

$abstractService = $root.'/src/Service/Http/Vendor/AbstractVendorCrudRouteService.php';
if (!is_file($abstractService)) {
    $errors[] = 'Missing AbstractVendorCrudRouteService.php';
} else {
    $source = file_get_contents($abstractService) ?: '';
    if (!str_contains($source, 'extends AbstractCrudEntrypointService')) {
        $errors[] = 'AbstractVendorCrudRouteService must extend AbstractCrudEntrypointService.';
    }
}

foreach (wave13PhpFiles($root.'/src/Service/Http/Vendor') as $file) {
    $relative = substr($file, strlen($root) + 1);
    $source = file_get_contents($file) ?: '';

    if (str_contains($source, 'use App\\Interfacing\\')) {
        $errors[] = 'Vendoring HTTP surface must not import Interfacing directly: '.$relative;
    }

    if (str_contains($source, 'render(') || str_contains($source, 'renderView(')) {
        $errors[] = 'Vendoring HTTP surface must not render directly: '.$relative;
    }

    if (str_ends_with($relative, 'AbstractVendorCrudRouteService.php')) {
        if (!preg_match('/\babstract\s+class\s+AbstractVendor[A-Za-z0-9_]*Service\b/', $source)) {
            $errors[] = 'Abstract support service must stay AbstractVendor*Service: '.$relative;
        }

        continue;
    }

    if (!preg_match('/\bfinal\s+(?:readonly\s+)?class\s+Vendor[A-Za-z0-9_]*Service\b/', $source)) {
        $errors[] = 'HTTP route target must be final Vendor*Service: '.$relative;
    }
}

foreach (wave13PhpFiles($root.'/src/Form/Vendor') as $file) {
    $relative = substr($file, strlen($root) + 1);
    $source = file_get_contents($file) ?: '';

    if (!preg_match('/\bfinal\s+class\s+Vendor[A-Za-z0-9_]*Type\b/', $source)) {
        $errors[] = 'Form route target must be final Vendor*Type: '.$relative;
    }
}

if (is_dir($root.'/src/Controller')) {
    $errors[] = 'Vendoring must remain zero-controller: src/Controller exists.';
}

if ($errors !== []) {
    fwrite(STDERR, "Vendoring Wave 13 Cruding stack alignment audit FAILED\n");
    foreach ($errors as $error) {
        fwrite(STDERR, ' - '.$error.PHP_EOL);
    }
    exit(1);
}

printf("Vendoring Wave 13 Cruding stack alignment audit OK\n");
printf("Route map files: %d\n", count($routeFiles));
printf("HTTP service targets: %d\n", count($serviceTargets));
printf("Form type targets: %d\n", count($formTargets));
printf("Controller files: 0\n");
