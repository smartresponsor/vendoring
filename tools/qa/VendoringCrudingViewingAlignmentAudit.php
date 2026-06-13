<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$serviceRoot = $root.'/src/Service/Http/Vendor';
$formRoot = $root.'/src/Form/Vendor';
$responseService = $serviceRoot.'/VendorHttpRouteResponseService.php';

/** @return list<string> */
function phpFiles(string $directory): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        if ($file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

$errors = [];

if (!is_file($responseService)) {
    $errors[] = 'Missing VendorHttpRouteResponseService.php';
} else {
    $responseSource = file_get_contents($responseService) ?: '';
    foreach (['crudingContract', 'viewingContract', 'interfacingDependency', 'controllerAllowed'] as $needle) {
        if (!str_contains($responseSource, $needle)) {
            $errors[] = sprintf('Response service is missing %s marker.', $needle);
        }
    }
}

$serviceFiles = phpFiles($serviceRoot);
$formFiles = phpFiles($formRoot);
$controllerFiles = phpFiles($root.'/src/Controller');

foreach ($controllerFiles as $controllerFile) {
    $errors[] = 'Controller file is not allowed in zero-controller Vendoring: '.substr($controllerFile, strlen($root) + 1);
}

foreach ($serviceFiles as $file) {
    $source = file_get_contents($file) ?: '';
    $relative = substr($file, strlen($root) + 1);

    if (str_ends_with($relative, 'AbstractVendorCrudRouteService.php')) {
        if (!preg_match('/\babstract\s+class\s+AbstractVendor[A-Za-z0-9_]*Service\b/', $source)) {
            $errors[] = 'Abstract HTTP support service must remain AbstractVendor*Service: '.$relative;
        }
    } elseif (!preg_match('/\bclass\s+(Vendor[A-Za-z0-9_]*Service)\b/', $source, $match)) {
        $errors[] = 'HTTP service does not expose Vendor*Service class: '.$relative;
        continue;
    }

    if (str_contains($source, 'use App\\Interfacing\\')) {
        $errors[] = 'Vendoring service directly depends on Interfacing: '.$relative;
    }
}

foreach ($formFiles as $file) {
    $source = file_get_contents($file) ?: '';
    if (!preg_match('/\bclass\s+(Vendor[A-Za-z0-9_]*Type)\b/', $source)) {
        $errors[] = 'Form type does not expose Vendor*Type class: '.substr($file, strlen($root) + 1);
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Vendoring Cruding/View alignment audit FAILED\n");
    foreach ($errors as $error) {
        fwrite(STDERR, ' - '.$error."\n");
    }
    exit(1);
}

printf("Vendoring Cruding/View alignment audit OK\n");
printf("HTTP service files: %d\n", count($serviceFiles));
printf("Form type files: %d\n", count($formFiles));
printf("Controller files: %d\n", count($controllerFiles));
printf("Response contract: cruding + viewing markers present\n");
