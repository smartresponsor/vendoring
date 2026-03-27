<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$root = dirname(__DIR__, 2);

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDOUT, "vendor fixture load smoke skipped: pdo_sqlite is required\n");
    exit(0);
}

$databaseFile = tempnam(sys_get_temp_dir(), 'vendoring_fixture_load_');

if (false === $databaseFile) {
    fwrite(STDERR, "Unable to create temporary sqlite database.\n");
    exit(2);
}

$env = [
    'APP_ENV' => 'test',
    'APP_DEBUG' => '0',
    'APP_SECRET' => 'vendoring-fixture-secret',
    'VENDOR_DSN' => 'sqlite:///'.$databaseFile,
    'VENDOR_SQLITE_DSN' => 'sqlite:///'.$databaseFile,
];

$commands = [
    ['php', 'bin/console', 'doctrine:schema:create', '--no-interaction'],
    ['php', 'bin/console', 'doctrine:fixtures:load', '--no-interaction'],
];

foreach ($commands as $command) {
    $process = new Process($command, $root, $env + $_ENV);
    $process->run();

    if (!$process->isSuccessful()) {
        @unlink($databaseFile);
        fwrite(STDERR, $process->getErrorOutput().$process->getOutput());
        exit($process->getExitCode() ?? 1);
    }
}

@unlink($databaseFile);

fwrite(STDOUT, "vendor fixture load smoke passed\n");
