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

$files = [];

foreach ($targets as $target) {
    if (!is_dir($target) && !is_file($target)) {
        continue;
    }

    if (is_file($target)) {
        if (pathinfo($target, PATHINFO_EXTENSION) === 'php') {
            $files[] = $target;
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

        $files[] = $file->getPathname();
    }
}

sort($files, SORT_STRING);

$phpBinary = PHP_BINARY;
$errors = [];
$concurrency = max(1, min(8, count($files)));

foreach (array_chunk($files, $concurrency) as $batch) {
    $processes = [];

    foreach ($batch as $pathname) {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open([$phpBinary, '-l', $pathname], $descriptorSpec, $pipes, $projectRoot);

        if (!is_resource($process)) {
            $errors[$pathname] = 'Failed to start PHP lint process.';
            continue;
        }

        fclose($pipes[0]);
        $processes[] = [
            'path' => $pathname,
            'process' => $process,
            'stdout' => $pipes[1],
            'stderr' => $pipes[2],
        ];
    }

    foreach ($processes as $entry) {
        $stdout = stream_get_contents($entry['stdout']);
        $stderr = stream_get_contents($entry['stderr']);
        fclose($entry['stdout']);
        fclose($entry['stderr']);
        $exitCode = proc_close($entry['process']);

        if ($exitCode === 0) {
            continue;
        }

        $message = trim(implode(PHP_EOL, array_filter([
            false === $stdout ? '' : trim($stdout),
            false === $stderr ? '' : trim($stderr),
        ])));
        $errors[$entry['path']] = '' === $message ? sprintf('PHP lint exited with code %d.', $exitCode) : $message;
    }
}

echo sprintf("Vendoring PHP lint checked %d file(s).\n", count($files));

if ($errors !== []) {
    echo "\nSyntax errors found:\n\n";

    foreach ($errors as $file => $message) {
        echo $file . PHP_EOL;
        echo $message . PHP_EOL . PHP_EOL;
    }

    exit(1);
}

echo "Vendoring PHP lint passed.\n";
