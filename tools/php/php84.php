<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

/**
 * @param list<string> $candidates
 */
function resolveBinary(array $candidates): string
{
    foreach ($candidates as $candidate) {
        if ($candidate === '') {
            continue;
        }

        if (str_contains($candidate, DIRECTORY_SEPARATOR) || str_contains($candidate, ':\\')) {
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }

            continue;
        }

        $resolved = trim((string) shell_exec(sprintf('command -v %s 2>/dev/null', escapeshellarg($candidate))));
        if ($resolved !== '') {
            return $resolved;
        }
    }

    fwrite(STDERR, "Unable to locate a PHP 8.4 binary.\n");
    exit(127);
}

$binary = match (PHP_OS_FAMILY) {
    'Windows' => resolveBinary([
        (string) getenv('PHP84_BINARY'),
        'C:\\PHP\\php-8.4.13-nts-Win32-vs17-x64\\php.exe',
        'php',
    ]),
    default => resolveBinary([
        (string) getenv('PHP84_BINARY'),
        '/usr/bin/php8.4',
        'php8.4',
        'php',
    ]),
};

$args = $argv;
array_shift($args);

$command = escapeshellarg($binary);

foreach ($args as $arg) {
    $command .= ' ' . escapeshellarg($arg);
}

passthru($command, $exitCode);
exit($exitCode);
