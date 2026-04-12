<?php

declare(strict_types=1);

require_once __DIR__ . '/_composer_json.php';

$root = dirname(__DIR__, 2);
$composer = vendoring_load_composer_json($root);

$expectedScripts = [
    'test:no-stub-config' => [
        'php tests/bin/no-stub-config-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalNoStubConfigContractTest',
    ],
    'test:no-placeholder-source' => [
        'php tests/bin/no-placeholder-source-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalNoPlaceholderSourceContractTest',
    ],
    'test:no-stub-source' => [
        'php tests/bin/no-stub-source-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalNoStubSourceContractTest',
    ],
    'test:no-placeholder-repository' => [
        'php tests/bin/no-placeholder-repository-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalNoPlaceholderRepositoryContractTest',
    ],
    'test:no-stub-repository' => [
        'php tests/bin/no-stub-repository-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalNoStubRepositoryContractTest',
    ],
    'test:no-example-config' => [
        'php tests/bin/no-example-config-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalNoExampleConfigContractTest',
    ],
    'test:no-example-repository' => [
        'php tests/bin/no-example-repository-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalNoExampleRepositoryContractTest',
    ],
    'test:no-example-wording-repository' => [
        'php tests/bin/no-example-wording-repository-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalNoExampleWordingRepositoryContractTest',
    ],
    'test:app-namespace-repository' => [
        'php tests/bin/app-namespace-repository-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalAppNamespaceRepositoryContractTest',
    ],
    'test:no-example-command-help' => [
        'php tests/bin/no-example-command-help-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalNoExampleCommandHelpContractTest',
    ],
    'test:no-legacy-vendor-script' => [
        'php tests/bin/no-legacy-vendor-script-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalNoLegacyVendorScriptContractTest',
    ],
    'test:composer-guard-parity' => [
        'php tests/bin/composer-guard-parity-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter ComposerGuardScriptParityTest',
    ],
];

foreach ($expectedScripts as $name => $commands) {
    if (vendoring_script_commands($composer, $name) !== $commands) {
        fwrite(STDERR, '[FAIL] composer script mismatch: ' . $name . PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, '[OK] ' . $name . ' uses canonical smoke + unit/filter pattern' . PHP_EOL);
}

$scripts = vendoring_composer_section($composer, 'scripts');
$quality = vendoring_string_list($scripts['quality'] ?? null);

if (!in_array('@test:composer-guard-parity', $quality, true)) {
    fwrite(STDERR, '[FAIL] quality pipeline missing @test:composer-guard-parity' . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, '[OK] quality pipeline includes composer guard parity slice' . PHP_EOL);
