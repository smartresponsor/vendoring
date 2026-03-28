<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

const EXIT_BINARY_NOT_FOUND = 127;
const REQUIRE_EXT_OPTION = '--require-ext=';
const ENV_PHP84_BINARY = 'PHP84_BINARY';
const PHP84_WINDOWS_CANDIDATES = [
    'C:\\PHP\\php-8.4.13-nts-Win32-vs17-x64\\php.exe',
    'php',
];
const PHP84_UNIX_CANDIDATES = [
    '/usr/bin/php8.4',
    'php8.4',
    'php',
];
const DOCKER_FALLBACK_ENV = [
    'PANTHER_NO_SANDBOX=1',
    'PANTHER_CHROME_BINARY=/usr/bin/chromium',
];

final class BinaryResolutionException extends \RuntimeException
{
}

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

        $resolved = trim((string) shellExec(sprintf('command -v %s 2>/dev/null', escapeshellarg($candidate))));
        if ($resolved !== '') {
            return $resolved;
        }
    }

    throw new BinaryResolutionException('Unable to locate a PHP 8.4 binary.');
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

    return passthruCommand($command);
}

function projectRoot(): string
{
    return dirname(__DIR__, 2);
}

function hasDockerBinary(): bool
{
    return trim((string) shellExec('command -v docker 2>/dev/null')) !== '';
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

function shellExec(string $command): string
{
    return (string) shell_exec($command);
}

function passthruCommand(string $command): int
{
    passthru($command, $exitCode);

    return $exitCode;
}

/**
 * @param list<string> $args
 * @return array{requiredExtensions:list<string>, commandArgs:list<string>}
 */
function parseArguments(array $args): array
{
    $requiredExtensions = [];

    while ($args !== [] && str_starts_with($args[0], REQUIRE_EXT_OPTION)) {
        $option = array_shift($args);
        if ($option === null) {
            break;
        }

        foreach (explode(',', substr($option, strlen(REQUIRE_EXT_OPTION))) as $extension) {
            $extension = trim($extension);
            if ($extension !== '') {
                $requiredExtensions[$extension] = true;
            }
        }
    }

    return [
        'requiredExtensions' => array_keys($requiredExtensions),
        'commandArgs' => array_values($args),
    ];
}

/**
 * @param list<string> $args
 */
function buildPhpCommand(string $binary, array $args): string
{
    $command = escapeshellarg($binary);

    foreach ($args as $arg) {
        $command .= ' ' . escapeshellarg($arg);
    }

    return $command;
}

function resolvePhp84Binary(): string
{
    $candidates = match (PHP_OS_FAMILY) {
        'Windows' => PHP84_WINDOWS_CANDIDATES,
        default => PHP84_UNIX_CANDIDATES,
    };

    array_unshift($candidates, (string) getenv(ENV_PHP84_BINARY));

    return resolveBinary($candidates);
}

/**
 * @param list<string> $argv
 */
function main(array $argv): int
{
    array_shift($argv);

    $parsed = parseArguments($argv);
    $missing = missingExtensions($parsed['requiredExtensions']);

    if ($missing !== []) {
        return runInDocker($parsed['commandArgs']);
    }

    try {
        return passthruCommand(buildPhpCommand(resolvePhp84Binary(), $parsed['commandArgs']));
    } catch (BinaryResolutionException $exception) {
        fwrite(STDERR, $exception->getMessage() . "\n");

        return EXIT_BINARY_NOT_FOUND;
    }
}

exit(main($argv));
