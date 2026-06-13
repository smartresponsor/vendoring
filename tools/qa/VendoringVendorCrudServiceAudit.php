<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];

$required = [
    'src/Service/Crud/VendorCrudService.php',
    'src/ServiceInterface/Crud/VendorCrudServiceInterface.php',
    'tests/Unit/Service/VendorCrudServiceTest.php',
];

$forbidden = [
    'src/Service/Core/VendorCoreService.php',
    'src/ServiceInterface/Core/VendorCoreServiceInterface.php',
    'tests/Unit/Service/VendorCoreServiceTest.php',
];

foreach ($required as $relative) {
    if (!is_file($root.'/'.$relative)) {
        $errors[] = 'Missing: '.$relative;
    }
}

foreach ($forbidden as $relative) {
    if (is_file($root.'/'.$relative)) {
        $errors[] = 'Old file remains: '.$relative;
    }
}

$scanRoots = [
    $root.'/src',
    $root.'/config',
    $root.'/tests',
];

foreach ($scanRoots as $scanRoot) {
    if (!is_dir($scanRoot)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanRoot));
    foreach ($iterator as $file) {
        if (!$file->isFile() || !in_array($file->getExtension(), ['php', 'yaml', 'yml', 'xml'], true)) {
            continue;
        }

        $source = file_get_contents($file->getPathname()) ?: '';
        if (str_contains($source, 'VendorCoreService')) {
            $errors[] = 'Old executable VendorCoreService reference: '.$file->getPathname();
        }
    }
}

$crud = file_get_contents($root.'/src/Service/Crud/VendorCrudService.php') ?: '';
foreach (['function index(', 'function find(', 'function create(', 'function update('] as $needle) {
    if (!str_contains($crud, $needle)) {
        $errors[] = 'VendorCrudService missing method: '.$needle;
    }
}

$entrypoint = file_get_contents($root.'/src/Service/Http/Vendor/AbstractVendorCrudRouteService.php') ?: '';
foreach (['vendorCrudService->index()', 'vendorCrudService->find(', 'vendorCrudService->create(', 'vendorCrudService->update('] as $needle) {
    if (!str_contains($entrypoint, $needle)) {
        $errors[] = 'Entrypoint not wired: '.$needle;
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Vendoring VendorCrudService audit OK\n");
fwrite(STDOUT, "Business operations: index, find, create, update\n");
fwrite(STDOUT, "HTTP entrypoints: wired through VendorCrudServiceInterface\n");
fwrite(STDOUT, "Old executable VendorCoreService references: 0\n");
