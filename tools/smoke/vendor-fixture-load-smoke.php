<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

require dirname(__DIR__, 2) . '/vendor/autoload.php';
require __DIR__ . '/vendor-fixture-smoke-lib.php';

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDOUT, "vendor fixture load smoke skipped: pdo_sqlite is required\n");
    exit(0);
}

vendoringFixtureSmokeRun('vendoring_fixture_load_', [
    ['php', 'bin/console', 'doctrine:schema:create', '--no-interaction'],
    ['php', 'bin/console', 'doctrine:fixtures:load', '--no-interaction'],
]);

fwrite(STDOUT, "vendor fixture load smoke passed\n");
