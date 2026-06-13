<?php

declare(strict_types=1);

require_once __DIR__.'/_composer_json.php';

$root = dirname(__DIR__, 2);
$composer = vendoring_load_composer_json($root);
$scripts = vendoring_composer_section($composer, 'scripts');
$encoded = json_encode($scripts, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

$forbidden = [
    'test:surface-builder',
    '@test:surface-builder',
    '--filter Controller',
    'ControllerInfrastructureContractTest',
];

foreach ($forbidden as $needle) {
    if (str_contains($encoded, $needle)) {
        fwrite(STDERR, 'Composer scripts contain forbidden controller-era vocabulary: '.$needle.PHP_EOL);
        exit(1);
    }
}

if (!array_key_exists('test:http-service-coverage', $scripts)) {
    fwrite(STDERR, 'Missing composer script: test:http-service-coverage'.PHP_EOL);
    exit(1);
}

$expected = [
    'php tests/bin/vendor-route-map-coverage-smoke.php',
    'php tests/bin/vendor-business-route-map-smoke.php',
    'php tests/bin/vendor-attachment-route-map-smoke.php',
];

$actual = vendoring_script_commands($composer, 'test:http-service-coverage');
foreach ($expected as $entry) {
    if (!in_array($entry, $actual, true)) {
        fwrite(STDERR, 'Missing canonical http-service coverage entry: '.$entry.PHP_EOL);
        exit(1);
    }
}

echo "Composer zero-controller vocabulary smoke passed.\n";
