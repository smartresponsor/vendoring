<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

const VENDOR_FIXTURE_SMOKE_APP_SECRET = 'vendoring-fixture-secret';

/**
 * @return array{root:string, databaseFile:string, env:array<string, string>}
 */
function vendoringFixtureSmokeBootstrap(string $databasePrefix): array
{
    $root = dirname(__DIR__, 2);

    if (!extension_loaded('pdo_sqlite')) {
        throw new RuntimeException('pdo_sqlite is required');
    }

    $databaseFile = tempnam(sys_get_temp_dir(), $databasePrefix);

    if (false === $databaseFile) {
        throw new RuntimeException('Unable to create temporary sqlite database.');
    }

    return [
        'root' => $root,
        'databaseFile' => $databaseFile,
        'env' => [
            'APP_ENV' => 'test',
            'APP_DEBUG' => '0',
            'APP_SECRET' => VENDOR_FIXTURE_SMOKE_APP_SECRET,
            'VENDOR_DSN' => 'sqlite:///'.$databaseFile,
            'VENDOR_SQLITE_DSN' => 'sqlite:///'.$databaseFile,
        ],
    ];
}

/**
 * @param list<list<string>> $commands
 * @param array<string, string> $environment
 */
function vendoringFixtureSmokeRunCommands(array $commands, string $root, array $environment): void
{
    foreach ($commands as $command) {
        $process = new Process($command, $root, $environment + $_ENV);
        $process->run();

        if ($process->isSuccessful()) {
            continue;
        }

        fwrite(STDERR, $process->getErrorOutput().$process->getOutput());
        exit($process->getExitCode() ?? 1);
    }
}

/**
 * @param list<list<string>> $commands
 */
function vendoringFixtureSmokeRun(string $databasePrefix, array $commands): void
{
    $bootstrap = vendoringFixtureSmokeBootstrap($databasePrefix);

    try {
        vendoringFixtureSmokeRunCommands(
            $commands,
            $bootstrap['root'],
            $bootstrap['env'],
        );
    } finally {
        @unlink($bootstrap['databaseFile']);
    }
}
