<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];

$controllerFiles = [];
$src = $root . '/src';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());
    $relative = substr($path, strlen($root) + 1);
    $contents = file_get_contents($path) ?: '';

    if (str_contains($relative, '/Controller/') || preg_match('/class\s+\w*Controller\b/', $contents)) {
        $controllerFiles[] = $relative;
    }
}

if ($controllerFiles !== []) {
    $errors[] = 'Controller surface detected: ' . implode(', ', $controllerFiles);
}

$routeFiles = array_merge(
    glob($root . '/config/platform/routes/**/*.yaml') ?: [],
    glob($root . '/config/platform/routes/*.yaml') ?: []
);

$serviceTargets = [];
$typeTargets = [];
foreach ($routeFiles as $routeFile) {
    $lines = file($routeFile, FILE_IGNORE_NEW_LINES) ?: [];
    foreach ($lines as $line) {
        if (preg_match_all('/service:\s*(App\\\\Service\\\\Http\\\\Vendor\\\\[A-Za-z0-9_\\\\]+)/', $line, $matches)) {
            foreach ($matches[1] as $target) {
                $serviceTargets[$target] = true;
            }
        }
        if (preg_match_all('/type:\s*(App\\\\Form\\\\Vendor\\\\[A-Za-z0-9_\\\\]+)/', $line, $matches)) {
            foreach ($matches[1] as $target) {
                $typeTargets[$target] = true;
            }
        }
    }
}

foreach (array_keys($serviceTargets) as $target) {
    $short = substr(strrchr($target, '\\') ?: $target, 1);
    if (!preg_match('/^Vendor[A-Za-z0-9]*Service$/', $short)) {
        $errors[] = 'Non-canonical service entrypoint: ' . $target;
    }

    $path = $root . '/src/' . str_replace('App\\', '', $target) . '.php';
    $path = str_replace('\\', '/', $path);
    if (!is_file($path)) {
        $errors[] = 'Missing service target file: ' . $target;
    }
}

foreach (array_keys($typeTargets) as $target) {
    $short = substr(strrchr($target, '\\') ?: $target, 1);
    if (!preg_match('/^Vendor[A-Za-z0-9]*Type$/', $short)) {
        $errors[] = 'Non-canonical form type entrypoint: ' . $target;
    }

    $path = $root . '/src/' . str_replace('App\\', '', $target) . '.php';
    $path = str_replace('\\', '/', $path);
    if (!is_file($path)) {
        $errors[] = 'Missing form type target file: ' . $target;
    }
}

$legacyFactory = $root . '/src/Service/Http/Vendor/VendorReadRouteResponseFactory.php';
if (is_file($legacyFactory)) {
    $errors[] = 'Legacy non-Service helper remains: src/Service/Http/Vendor/VendorReadRouteResponseFactory.php';
}

if ($errors !== []) {
    fwrite(STDERR, "Vendoring zero-controller surface audit FAILED\n" . implode("\n", $errors) . "\n");
    exit(1);
}

echo "Vendoring zero-controller surface audit OK\n";
echo 'Service targets: ' . count($serviceTargets) . "\n";
echo 'Form type targets: ' . count($typeTargets) . "\n";
echo "Controller files: 0\n";
