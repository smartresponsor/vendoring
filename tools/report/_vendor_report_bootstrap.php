<?php

declare(strict_types=1);

function vendorReportProjectRoot(): string
{
    $root = realpath(__DIR__.'/../..');
    if (!is_string($root) || '' === $root) {
        fwrite(STDERR, "Unable to resolve project root.
");
        exit(2);
    }

    return $root;
}

function vendorReportRunScript(string $relativePath): int
{
    $root = vendorReportProjectRoot();
    $path = $root.'/'.ltrim($relativePath, '/');

    if (!is_file($path)) {
        fwrite(STDERR, sprintf("Missing report target: %s
", $relativePath));
        return 1;
    }

    passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg($path), $exitCode);

    return (int) $exitCode;
}

function vendorReportHeader(string $title): void
{
    echo $title, PHP_EOL;
    echo str_repeat('=', strlen($title)), PHP_EOL;
}

function vendorReportSection(string $title): void
{
    echo PHP_EOL, '[', $title, ']', PHP_EOL;
}

function vendorReportPrintCheck(string $label, bool $ok, ?string $detail = null): void
{
    echo $ok ? '[OK] ' : '[WARN] ', $label;
    if (null !== $detail && '' !== $detail) {
        echo ' - ', $detail;
    }
    echo PHP_EOL;
}

function vendorReportHasNonEmptyFile(string $relativePath): bool
{
    $root = vendorReportProjectRoot();
    $path = $root.'/'.ltrim($relativePath, '/');

    return is_file($path) && '' !== trim((string) file_get_contents($path));
}

function vendorReportFileExists(string $relativePath): bool
{
    return is_file(vendorReportProjectRoot().'/'.ltrim($relativePath, '/'));
}
