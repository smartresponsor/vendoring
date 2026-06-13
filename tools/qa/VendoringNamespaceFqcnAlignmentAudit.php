<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];
$checkedPhp = 0;
$routeTargets = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root.'/src', FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    ++$checkedPhp;
    $path = $file->getPathname();
    $content = file_get_contents($path);

    if ($content === false) {
        $errors[] = 'Unreadable PHP file: '.$path;
        continue;
    }

    if (str_contains($content, 'App\\Service\\Http\\') || str_contains($content, 'App\\Form\\')) {
        $errors[] = 'Legacy non-component FQCN remains: '.$path;
    }

    if (preg_match('/namespace\s+([^;]+);/', $content, $match) !== 1) {
        continue;
    }

    $relativeDirectory = trim(str_replace('\\', '/', dirname(substr($path, strlen($root.'/src/')))), './');
    $expectedNamespace = 'App\\Vendoring'.($relativeDirectory === '' ? '' : '\\'.str_replace('/', '\\', $relativeDirectory));

    if ($match[1] !== $expectedNamespace) {
        $errors[] = sprintf(
            'PSR-4 mismatch: %s has namespace %s; expected %s',
            substr($path, strlen($root) + 1),
            $match[1],
            $expectedNamespace
        );
    }
}

$routeDirectory = $root.'/config/platform/routes';
if (is_dir($routeDirectory)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($routeDirectory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || !in_array($file->getExtension(), ['yaml', 'yml'], true)) {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            continue;
        }

        preg_match_all('/(?:service|type):\s*([A-Za-z0-9_\\\\]+)/', $content, $matches);
        foreach ($matches[1] as $fqcn) {
            $routeTargets[$fqcn] = true;
        }
    }
}

foreach (array_keys($routeTargets) as $fqcn) {
    if (!str_starts_with($fqcn, 'App\\Vendoring\\')) {
        $errors[] = 'Route target is outside App\\Vendoring namespace: '.$fqcn;
        continue;
    }

    $relative = substr($fqcn, strlen('App\\Vendoring\\'));
    $path = $root.'/src/'.str_replace('\\', '/', $relative).'.php';

    if (!is_file($path)) {
        $errors[] = 'Route target file missing: '.$fqcn.' -> '.substr($path, strlen($root) + 1);
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Vendoring namespace/FQCN alignment audit FAILED\n");
    foreach ($errors as $error) {
        fwrite(STDERR, ' - '.$error."\n");
    }
    exit(1);
}

printf(
    "Vendoring namespace/FQCN alignment audit OK\nPHP files checked: %d\nRoute targets checked: %d\n",
    $checkedPhp,
    count($routeTargets)
);
