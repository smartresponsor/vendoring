<?php

declare(strict_types=1);

require_once __DIR__.'/_composer_json.php';

$composer = vendoring_load_composer_json(dirname(__DIR__, 2));
$quality = vendoring_script_commands($composer, 'quality');

foreach ($quality as $entry) {
    if (str_contains($entry, 'composer test:')) {
        fwrite(STDERR, 'quality must not shell-call composer test:* directly: '.$entry.PHP_EOL);
        exit(1);
    }
    if (str_contains($entry, '&&')) {
        fwrite(STDERR, 'quality must not chain test scripts inline: '.$entry.PHP_EOL);
        exit(1);
    }
}

$expected = [
    '@test:symfony-stack',
    '@test:di',
    '@test:entrypoint',
    '@lint:php',
    '@test:compat',
    '@test:smoke',
    '@phpstan',
    '@test:mail',
    '@test:statement-command',
    '@test:statement',
    '@test:payout',
    '@test:repository',
    '@test:unit',
    '@test:controller',
    '@test:entity',
    '@test:transaction-persistence',
    '@test:transaction-amount',
    '@test:transaction-status-persistence',
    '@test:transaction-doctrine',
    '@test:transaction',
    '@test:transaction-policy',
    '@test:transaction-migration',
    '@test:transaction-idempotency',
    '@test:transaction-identity',
    '@test:transaction-error-surface',
    '@test:transaction-mapping',
    '@test:transaction-schema-parity',
    '@test:transaction-uniqueness-contract',
    '@test:transaction-json',
    '@test:root-structure',
    '@test:root-protocol-cleanup',
    '@test:root-vendor-cleanup',
    '@test:root-removed-files',
    '@test:root-runtime-artifacts',
    '@test:idea-runtime-artifact',
    '@test:idea-module-artifact',
    '@test:no-stub-config',
    '@test:no-placeholder-source',
    '@test:no-placeholder-repository',
    '@test:no-stub-source',
    '@test:no-stub-repository',
    '@test:no-example-config',
    '@test:no-example-repository',
    '@test:no-example-wording-repository',
    '@test:no-example-command-help',
    '@test:app-namespace-repository',
    '@test:no-legacy-vendor-script',
    '@test:composer-guard-parity',
    '@test:composer-root-guard-parity',
    '@test:composer-quality-parity',
    '@test:composer-script-invocation-parity',
];

foreach ($expected as $scriptName) {
    if (!in_array($scriptName, $quality, true)) {
        fwrite(STDERR, 'Missing canonical quality entry: '.$scriptName.PHP_EOL);
        exit(1);
    }
}

echo 'composer quality parity full-chain OK
';
