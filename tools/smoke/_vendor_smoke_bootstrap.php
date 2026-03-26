<?php

declare(strict_types=1);

function vendorSmokeProjectRoot(): string
{
    $root = realpath(__DIR__.'/../..');
    if (!is_string($root) || '' === $root) {
        fwrite(STDERR, "Unable to resolve project root.
");
        exit(2);
    }

    return $root;
}

function vendorSmokeRunPhpScript(string $relativePath): never
{
    $root = vendorSmokeProjectRoot();
    $path = $root.'/'.ltrim($relativePath, '/');

    if (!is_file($path)) {
        fwrite(STDERR, sprintf("Missing smoke target: %s
", $relativePath));
        exit(1);
    }

    passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg($path), $exitCode);
    exit((int) $exitCode);
}
