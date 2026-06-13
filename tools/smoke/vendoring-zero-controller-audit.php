<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$failures = [];

$forbiddenPaths = [
    $root . '/src/Controller',
    $root . '/src/ControllerTrait',
    $root . '/config/vendor_routes.yaml',
];

foreach ($forbiddenPaths as $forbiddenPath) {
    if (file_exists($forbiddenPath)) {
        $failures[] = 'Forbidden controller-layer artifact must be removed: ' . substr($forbiddenPath, strlen($root) + 1);
    }
}

$componentRoutes = $root . '/config/component/routes.yaml';
if (!is_file($componentRoutes)) {
    $failures[] = 'Missing config/component/routes.yaml.';
} else {
    $contents = (string) file_get_contents($componentRoutes);
    if (str_contains($contents, 'vendor_routes.yaml')) {
        $failures[] = 'config/component/routes.yaml must not import config/vendor_routes.yaml.';
    }
    if (str_contains($contents, 'controller:') || str_contains($contents, 'Controller')) {
        $failures[] = 'config/component/routes.yaml must not reference controller routes.';
    }
}

$services = $root . '/config/component/services.yaml';
if (!is_file($services)) {
    $failures[] = 'Missing config/component/services.yaml.';
} else {
    $contents = (string) file_get_contents($services);

    if (!str_contains($contents, "- '../../src/Service/Http/'")) {
        $failures[] = "config/component/services.yaml must exclude ../../src/Service/Http/ from App\\Vendoring\\ resource; App\\Vendoring\\Service\\Http\\ owns it.";
    }
    if (!str_contains($contents, 'App\\Vendoring\\Service\\Http\\:')) {
        $failures[] = 'config/component/services.yaml must export App\\Vendoring\\Service\\Http\\ for Cruding FQCN convention.';
    }
    if (!str_contains($contents, 'App\\Vendoring\\Form\\:')) {
        $failures[] = 'config/component/services.yaml must export App\\Vendoring\\Form\\ for canonical Type classes.';
    }
    if (str_contains($contents, '../../src/Controller') || str_contains($contents, '..\\..\\src\\Controller')) {
        $failures[] = 'config/component/services.yaml must not reference removed controller paths.';
    }
}

$expectedFiles = [
    'src/Service/Http/Vendor/VendorIndexService.php',
    'src/Service/Http/Vendor/VendorShowService.php',
    'src/Service/Http/Vendor/VendorCreateService.php',
    'src/Service/Http/Vendor/Attachment/Document/VendorAttachmentDocumentIndexService.php',
    'src/Service/Http/Vendor/Attachment/Document/VendorAttachmentDocumentShowService.php',
    'src/Service/Http/Vendor/Attachment/Media/VendorAttachmentMediaIndexService.php',
    'src/Service/Http/Vendor/Attachment/Media/VendorAttachmentMediaShowService.php',
    'src/Service/Http/Vendor/Category/VendorCategoryAssignService.php',
    'src/Service/Http/Vendor/Document/VendorDocumentVerifyService.php',
    'src/Service/Http/Vendor/Payout/VendorPayoutCalculateService.php',
    'src/Service/Http/Vendor/Product/VendorProductAssignService.php',
    'src/Form/Vendor/VendorCreateType.php',
    'src/Form/Vendor/Category/VendorCategoryAssignType.php',
    'src/Form/Vendor/Payout/VendorPayoutCalculateType.php',
    'config/platform/routes.crud.yaml',
    'config/platform/routes.business.yaml',
    'config/platform/routes.platform.yaml',
    'tests/bin/vendor-route-map-coverage-smoke.php',
];

foreach ($expectedFiles as $expectedFile) {
    if (!is_file($root . '/' . $expectedFile)) {
        $failures[] = 'Missing canonical zero-controller artifact: ' . $expectedFile;
    }
}

$srcIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/src', FilesystemIterator::SKIP_DOTS));
foreach ($srcIterator as $fileInfo) {
    if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile() || 'php' !== $fileInfo->getExtension()) {
        continue;
    }

    $relative = substr($fileInfo->getPathname(), strlen($root) + 1);
    $contents = (string) file_get_contents($fileInfo->getPathname());

    if (str_starts_with($relative, 'src/Service/Http/') && str_contains($contents, 'namespace App\\Vendoring\\Service\\Http')) {
        $failures[] = 'Service/Http file must use App\\Service\\Http namespace for Cruding FQCN convention: ' . $relative;
    }
    if (str_starts_with($relative, 'src/Service/Http/') && str_contains($contents, 'use App\\Vendoring\\Service\\Http')) {
        $failures[] = 'Service/Http file must not import App\\Vendoring\\Service\\Http after canonical namespace migration: ' . $relative;
    }
    if (str_contains($contents, 'Symfony\\Component\\Routing\\Annotation\\Route') || str_contains($contents, 'Symfony\\Component\\Routing\\Attribute\\Route')) {
        $failures[] = 'Forbidden Symfony Route import in zero-controller component: ' . $relative;
    }
    if (str_contains($contents, '#[Route(')) {
        $failures[] = 'Forbidden Symfony Route attribute usage in zero-controller component: ' . $relative;
    }
}

foreach ([$root . '/config', $root . '/tests/Unit', $root . '/tools/report'] as $scanRoot) {
    if (!is_dir($scanRoot)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile()) {
            continue;
        }
        $path = $fileInfo->getPathname();
        if (!str_ends_with($path, '.php') && !str_ends_with($path, '.yaml') && !str_ends_with($path, '.yml')) {
            continue;
        }
        $contents = (string) file_get_contents($path);
        if (str_contains($contents, 'vendor_routes.yaml')) {
            $failures[] = $path . ' contains forbidden vendor_routes.yaml reference';
        }
        if (str_contains($contents, 'src/Controller') || str_contains($contents, 'src\\Controller') || str_contains($contents, 'Controller\\Vendor')) {
            $failures[] = $path . ' contains forbidden controller-layer reference';
        }
    }
}

if ([] !== $failures) {
    fwrite(STDERR, "Vendoring zero-controller audit failed:\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, ' - ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo "Vendoring zero-controller audit passed.\n";
