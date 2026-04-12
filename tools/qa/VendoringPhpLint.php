<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);

$targets = [
    $projectRoot . '/src',
    $projectRoot . '/tests',
    $projectRoot . '/tools',
    $projectRoot . '/config',
    $projectRoot . '/bin',
];

$phpBinary = PHP_BINARY;
$errors = [];
$checked = 0;

foreach ($targets as $target) {
    if (!is_dir($target) && !is_file($target)) {
        continue;
    }

    if (is_file($target)) {
        if (pathinfo($target, PATHINFO_EXTENSION) !== 'php') {
            continue;
        }

        $checked++;
        $output = [];
        $exitCode = 0;
        exec(sprintf('"%s" -l "%s" 2>&1', $phpBinary, $target), $output, $exitCode);

        if ($exitCode !== 0) {
            $errors[$target] = implode(PHP_EOL, $output);
        }

        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $pathname = $file->getPathname();
        $checked++;

        $output = [];
        $exitCode = 0;
        exec(sprintf('"%s" -l "%s" 2>&1', $phpBinary, $pathname), $output, $exitCode);

        if ($exitCode !== 0) {
            $errors[$pathname] = implode(PHP_EOL, $output);
        }
    }
}

echo sprintf("Vendoring PHP lint checked %d file(s).\n", $checked);

if ($errors !== []) {
    echo "\nSyntax errors found:\n\n";

    foreach ($errors as $file => $message) {
        echo $file . PHP_EOL;
        echo $message . PHP_EOL . PHP_EOL;
    }

    exit(1);
}

echo "Vendoring PHP lint passed.\n";
