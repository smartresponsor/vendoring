<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

const EXIT_BINARY_NOT_FOUND = 127;
const DOCKER_FALLBACK_ENV = [
    'PANTHER_NO_SANDBOX=1',
    'PANTHER_CHROME_BINARY=/usr/bin/chromium',
    'PANTHER_CHROME_DRIVER_BINARY=/usr/bin/chromedriver',
];

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
    exit(EXIT_BINARY_NOT_FOUND);
}

/**
 * @param list<string> $extensions
 */
function missingExtensions(array $extensions): array
{
    $missing = [];

    foreach ($extensions as $extension) {
        if ($extension !== '' && !extension_loaded($extension)) {
            $missing[] = $extension;
        }
    }

    return $missing;
}

/**
 * @param list<string> $args
 */
function runInDocker(array $args): int
{
    $root = projectRoot();
    chdir($root);

    if (!hasDockerBinary()) {
        fwrite(STDERR, "Missing required local PHP extensions and Docker is unavailable for fallback.\n");
        return EXIT_BINARY_NOT_FOUND;
    }

    $script = <<<'SH'
if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi
exec php "$@"
SH;

    $command = buildDockerCommand($script, $args);

    passthru($command, $exitCode);

    return $exitCode;
}

function projectRoot(): string
{
    return dirname(__DIR__, 2);
}

function hasDockerBinary(): bool
{
    return trim((string) shell_exec('command -v docker 2>/dev/null')) !== '';
}

/**
 * @param list<string> $args
 */
function buildDockerCommand(string $script, array $args): string
{
    $command = 'docker compose run --rm -T';

    foreach (DOCKER_FALLBACK_ENV as $env) {
        $command .= ' -e ' . escapeshellarg($env);
    }

    $command .= ' app sh -lc ' . escapeshellarg($script) . ' sh';

    foreach ($args as $arg) {
        $command .= ' ' . escapeshellarg($arg);
    }

    return $command;
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

$requiredExtensions = [];

while ($args !== [] && str_starts_with($args[0], '--require-ext=')) {
    $option = array_shift($args);
    if ($option === null) {
        break;
    }

    foreach (explode(',', substr($option, strlen('--require-ext='))) as $extension) {
        $extension = trim($extension);
        if ($extension !== '') {
            $requiredExtensions[$extension] = true;
        }
    }
}

$missing = missingExtensions(array_keys($requiredExtensions));

if ($missing !== []) {
    exit(runInDocker($args));
}

$command = escapeshellarg($binary);

foreach ($args as $arg) {
    $command .= ' ' . escapeshellarg($arg);
}

passthru($command, $exitCode);
exit($exitCode);
