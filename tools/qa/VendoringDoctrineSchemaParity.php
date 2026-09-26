<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$dsn = getenv('VENDOR_SCHEMA_PARITY_DSN');

if (false === $dsn || '' === trim($dsn)) {
    fwrite(STDERR, "VENDOR_SCHEMA_PARITY_DSN must point to a disposable PostgreSQL database whose name ends with _vendoring_parity.\n");
    exit(2);
}

$parts = parse_url($dsn);
if (!is_array($parts) || !in_array($parts['scheme'] ?? null, ['postgres', 'postgresql'], true)) {
    fwrite(STDERR, "VENDOR_SCHEMA_PARITY_DSN must be a PostgreSQL URL.\n");
    exit(2);
}

$database = ltrim((string) ($parts['path'] ?? ''), '/');
if (!str_ends_with($database, '_vendoring_parity')) {
    fwrite(STDERR, "Refusing schema parity against a database not ending in _vendoring_parity.\n");
    exit(2);
}

putenv('VENDOR_DSN='.$dsn);
$_ENV['VENDOR_DSN'] = $dsn;
$_SERVER['VENDOR_DSN'] = $dsn;

$commands = [
    ['doctrine:database:drop', '--if-exists', '--force'],
    ['doctrine:database:create'],
    ['doctrine:migrations:migrate', '--allow-no-migration'],
    ['doctrine:schema:validate'],
    ['doctrine:migrations:up-to-date'],
];

foreach ($commands as $arguments) {
    $command = array_merge([PHP_BINARY, $root.'/bin/console'], $arguments, ['--env=test', '--no-interaction']);
    $process = proc_open($command, [STDIN, STDOUT, STDERR], $pipes, $root);
    if (!is_resource($process)) {
        fwrite(STDERR, sprintf("Unable to start %s.\n", $arguments[0]));
        exit(1);
    }

    $exitCode = proc_close($process);
    if (0 !== $exitCode) exit($exitCode);
}

