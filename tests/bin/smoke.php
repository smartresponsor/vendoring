<?php

declare(strict_types=1);

require_once __DIR__ . '/_composer_json.php';

$root = dirname(__DIR__, 2);

$checks = [
    'composer.json exists' => is_file($root . '/composer.json'),
    'phpunit.xml.dist exists' => is_file($root . '/phpunit.xml.dist'),
    'phpstan.neon exists' => is_file($root . '/phpstan.neon'),
    'src directory exists' => is_dir($root . '/src'),
    'tests directory exists' => is_dir($root . '/tests'),
    'tests/Unit directory exists' => is_dir($root . '/tests/Unit'),
];

foreach ($checks as $label => $result) {
    if (true !== $result) {
        fwrite(STDERR, '[FAIL] ' . $label . PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, '[OK] ' . $label . PHP_EOL);
}

$composer = vendoring_load_composer_json($root);
$require = vendoring_composer_section($composer, 'require');
$scripts = vendoring_composer_scripts($composer);

if (($require['php'] ?? null) !== '^8.4') {
    fwrite(STDERR, '[FAIL] composer runtime php constraint must be ^8.4' . PHP_EOL);
    exit(1);
}

foreach (['lint:php', 'test:smoke', 'test:symfony-stack', 'test:di', 'test:entrypoint', 'test:mail', 'test:statement-command', 'test:statement', 'test:payout', 'test:controller', 'test:entity', 'test:compat', 'test:repository', 'test:unit', 'test:transaction-policy', 'test:transaction-amount', 'test:transaction-doctrine', 'test:transaction-migration', 'test:transaction-persistence', 'test:transaction-sqlite-integration', 'test:transaction-idempotency', 'test:transaction-identity', 'test:transaction-error-surface', 'test:transaction-json', 'test:transaction-mapping', 'test:transaction-status-persistence', 'test:root-vendor-cleanup', 'test:root-runtime-artifacts', 'phpstan', 'test', 'quality'] as $scriptName) {
    if (!array_key_exists($scriptName, $scripts)) {
        fwrite(STDERR, '[FAIL] missing composer script: ' . $scriptName . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, '[OK] composer runtime php constraint is ^8.4' . PHP_EOL);
fwrite(STDOUT, '[OK] quality scripts are registered' . PHP_EOL);
fwrite(STDOUT, '[OK] statement, payout, controller, and repository scripts are registered in master smoke' . PHP_EOL);
fwrite(STDOUT, '[OK] statement and payout smoke files are present' . PHP_EOL);

$requiredSymfony = ['symfony/console', 'symfony/framework-bundle', 'symfony/http-foundation', 'symfony/mailer', 'symfony/mime', 'symfony/routing', 'symfony/uid'];
foreach ($requiredSymfony as $packageName) {
    if (!isset($require[$packageName])) {
        fwrite(STDERR, '[FAIL] missing runtime package: ' . $packageName . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, '[OK] runtime Symfony packages are declared' . PHP_EOL);

$requiredDoctrine = ['doctrine/dbal', 'doctrine/doctrine-bundle', 'doctrine/orm', 'doctrine/persistence'];
foreach ($requiredDoctrine as $packageName) {
    if (!isset($require[$packageName])) {
        fwrite(STDERR, '[FAIL] missing runtime package: ' . $packageName . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, '[OK] runtime Doctrine packages are declared' . PHP_EOL);

if (!file_exists($root . '/tests/bin/interface-alias-smoke.php')) {
    fwrite(STDERR, 'Missing interface alias smoke script' . PHP_EOL);
    exit(1);
}

if (!file_exists($root . '/tests/bin/statement-service-smoke.php')) {
    fwrite(STDERR, 'Missing statement-service-smoke.php' . PHP_EOL);
    exit(1);
}

if (!file_exists($root . '/tests/bin/payout-service-smoke.php')) {
    fwrite(STDERR, 'Missing payout-service-smoke.php' . PHP_EOL);
    exit(1);
}

if (!file_exists($root . '/tests/bin/repository-contract-smoke.php')) {
    fwrite(STDERR, 'Missing repository contract smoke script' . PHP_EOL);
    exit(1);
}

if (!file_exists($root . '/tests/bin/entrypoint-contract-smoke.php')) {
    fwrite(STDERR, 'Missing entrypoint contract smoke script' . PHP_EOL);
    exit(1);
}

if (!file_exists($root . '/tests/bin/transaction-route-smoke.php')) {
    fwrite(STDERR, "Missing transaction-route-smoke.php\n");
    exit(1);
}

if (!vendoring_has_script($composer, 'test:transaction')) {
    fwrite(STDERR, '[FAIL] missing composer script: test:transaction' . PHP_EOL);
    exit(1);
}

if (!file_exists($root . '/tests/bin/transaction-policy-smoke.php')) {
    fwrite(STDERR, "Missing transaction-policy-smoke.php\n");
    exit(1);
}

if (!file_exists($root . '/tests/bin/transaction-amount-smoke.php')) {
    fwrite(STDERR, "Missing transaction-amount-smoke.php\n");
    exit(1);
}

if (!file_exists($root . '/tests/bin/transaction-migration-smoke.php')) {
    fwrite(STDERR, "Missing transaction-migration-smoke.php\n");
    exit(1);
}

if (!file_exists($root . '/tests/bin/transaction-persistence-smoke.php') || !file_exists($root . '/tests/bin/transaction-sqlite-integration-smoke.php')) {
    fwrite(STDERR, "Missing transaction persistence/sqlite integration smoke files\n");
    exit(1);
}

if (!file_exists($root . '/tests/bin/transaction-idempotency-smoke.php')) {
    fwrite(STDERR, "Missing transaction-idempotency-smoke.php\n");
    exit(1);
}

if (!file_exists($root . '/tests/bin/transaction-identity-smoke.php')) {
    fwrite(STDERR, "Missing transaction-identity-smoke.php\n");
    exit(1);
}

if (!file_exists($root . '/tests/bin/transaction-error-surface-smoke.php')) {
    fwrite(STDERR, "Missing transaction-error-surface-smoke.php\n");
    exit(1);
}

if (!file_exists($root . '/tests/bin/transaction-json-surface-smoke.php')) {
    fwrite(STDERR, "Missing transaction-json-surface-smoke.php\n");
    exit(1);
}

fwrite(STDOUT, '[OK] transaction route script plus migration, persistence, idempotency, identity, error-surface, and json smoke scripts are present' . PHP_EOL);

if (!file_exists($root . '/tests/bin/transaction-mapping-parity-smoke.php')) {
    fwrite(STDERR, "Missing transaction-mapping-parity-smoke.php\n");
    exit(1);
}

if (!file_exists(__DIR__ . '/transaction-status-persistence-smoke.php')) {
    fwrite(STDERR, "transaction-status-persistence-smoke.php missing\n");
    exit(1);
}

if (!file_exists($root . '/tests/bin/root-structure-smoke.php')) {
    fwrite(STDERR, "Missing root-structure-smoke.php\n");
    exit(1);
}

if (!vendoring_has_script($composer, 'test:root-structure')) {
    fwrite(STDERR, '[FAIL] missing composer script: test:root-structure' . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, '[OK] root structure smoke script and composer script are present' . PHP_EOL);

if (!file_exists($root . '/tests/bin/root-removed-files-smoke.php')) {
    fwrite(STDERR, "Missing root-removed-files-smoke.php\n");
    exit(1);
}

if (!vendoring_has_script($composer, 'test:root-removed-files')) {
    fwrite(STDERR, '[FAIL] missing composer script: test:root-removed-files' . PHP_EOL);
    exit(1);
}

if (!vendoring_has_script($composer, 'test:root-protocol-cleanup')) {
    fwrite(STDERR, '[FAIL] missing composer script: test:root-protocol-cleanup' . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, '[OK] root removed-files and root protocol cleanup scripts are present' . PHP_EOL);

if (!file_exists($root . '/tests/bin/no-stub-config-smoke.php')) {
    fwrite(STDERR, "Missing no-stub-config-smoke.php\n");
    exit(1);
}

if (!file_exists($root . '/tests/bin/no-placeholder-source-smoke.php')) {
    fwrite(STDERR, "Missing no-placeholder-source-smoke.php\n");
    exit(1);
}

if (!file_exists($root . '/tests/bin/no-placeholder-repository-smoke.php')) {
    fwrite(STDERR, "Missing no-placeholder-repository-smoke.php\n");
    exit(1);
}

if (!file_exists($root . '/tests/bin/no-stub-source-smoke.php')) {
    fwrite(STDERR, "Missing no-stub-source-smoke.php\n");
    exit(1);
}

foreach ([
    'tests/bin/no-example-config-smoke.php',
    'tests/bin/no-example-repository-smoke.php',
    'tests/bin/no-example-wording-repository-smoke.php',
    'tests/bin/app-namespace-repository-smoke.php',
    'tests/bin/idea-module-artifact-smoke.php',
    'tests/bin/no-stub-repository-smoke.php',
] as $requiredFile) {
    if (!file_exists($root . '/' . $requiredFile)) {
        fwrite(STDERR, '[FAIL] missing smoke script: ' . $requiredFile . PHP_EOL);
        exit(1);
    }
}

foreach ([
    'test:no-stub-config',
    'test:no-placeholder-source',
    'test:no-placeholder-repository',
    'test:no-stub-source',
    'test:no-stub-repository',
    'test:no-example-config',
    'test:no-example-repository',
    'test:no-example-wording-repository',
    'test:no-example-command-help',
    'test:app-namespace-repository',
    'test:idea-module-artifact',
] as $scriptName) {
    if (!array_key_exists($scriptName, $scripts)) {
        fwrite(STDERR, '[FAIL] missing composer script: ' . $scriptName . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, '[OK] no-stub/no-placeholder/no-example repository, source and config guard smoke files and scripts are present' . PHP_EOL);

if (!file_exists($root . '/tests/bin/idea-runtime-artifact-smoke.php')) {
    fwrite(STDERR, "Missing idea-runtime-artifact-smoke.php\n");
    exit(1);
}

if (!vendoring_has_script($composer, 'test:idea-runtime-artifact')) {
    fwrite(STDERR, "Missing composer script: test:idea-runtime-artifact\n");
    exit(1);
}

if (!vendoring_has_script($composer, 'test:idea-module-artifact')) {
    fwrite(STDERR, "Missing composer script: test:idea-module-artifact\n");
    exit(1);
}

fwrite(STDOUT, '[OK] IDE runtime and module artifact guard scripts are present' . PHP_EOL);

if (!file_exists($root . '/tests/bin/composer-script-invocation-parity-smoke.php')) {
    fwrite(STDERR, "Missing composer-script-invocation-parity-smoke.php\n");
    exit(1);
}

if (!vendoring_has_script($composer, 'test:composer-script-invocation-parity')) {
    fwrite(STDERR, "Missing test:composer-script-invocation-parity script\n");
    exit(1);
}

fwrite(STDOUT, '[OK] composer script invocation parity guard is present' . PHP_EOL);

if (!file_exists($root . '/tests/bin/composer-root-guard-parity-smoke.php')) {
    fwrite(STDERR, "Missing composer-root-guard-parity-smoke.php\n");
    exit(1);
}

if (!vendoring_has_script($composer, 'test:composer-root-guard-parity')) {
    fwrite(STDERR, "Missing composer script test:composer-root-guard-parity\n");
    exit(1);
}

fwrite(STDOUT, '[OK] composer root guard parity guard is present' . PHP_EOL);

if (!file_exists($root . '/tests/bin/composer-quality-parity-smoke.php')) {
    fwrite(STDERR, "Missing composer-quality-parity-smoke.php\n");
    exit(1);
}

if (!vendoring_has_script($composer, 'test:composer-quality-parity')) {
    fwrite(STDERR, "Missing composer script test:composer-quality-parity\n");
    exit(1);
}

fwrite(STDOUT, '[OK] composer quality parity guard is present' . PHP_EOL);

if (!file_exists($root . '/tests/bin/composer-guard-parity-smoke.php')) {
    fwrite(STDERR, "Missing composer-guard-parity-smoke.php\n");
    exit(1);
}

if (!vendoring_has_script($composer, 'test:composer-guard-parity')) {
    fwrite(STDERR, '[FAIL] missing composer script: test:composer-guard-parity' . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, '[OK] composer guard parity smoke script and composer script are present' . PHP_EOL);
fwrite(STDOUT, '[OK] repository contract smoke script and composer script are present' . PHP_EOL);

foreach ([
    'tests/bin/transaction-schema-parity-smoke.php' => 'test:transaction-schema-parity',
    'tests/bin/transaction-uniqueness-contract-smoke.php' => 'test:transaction-uniqueness-contract',
] as $requiredFile => $scriptName) {
    if (!file_exists($root . '/' . $requiredFile)) {
        fwrite(STDERR, '[FAIL] missing smoke script: ' . $requiredFile . PHP_EOL);
        exit(1);
    }

    if (!array_key_exists($scriptName, $scripts)) {
        fwrite(STDERR, '[FAIL] missing composer script: ' . $scriptName . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, '[OK] transaction schema parity and uniqueness contract guards are present' . PHP_EOL);

if (!file_exists($root . '/tests/bin/no-example-command-help-smoke.php')) {
    fwrite(STDERR, "Missing no-example-command-help-smoke.php\n");
    exit(1);
}

if (!vendoring_has_script($composer, 'test:no-example-command-help')) {
    fwrite(STDERR, '[FAIL] missing composer script: test:no-example-command-help' . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, '[OK] no-example-command-help smoke script and composer script are present' . PHP_EOL);

if (!file_exists($root . '/tests/bin/no-legacy-vendor-script-smoke.php')) {
    fwrite(STDERR, "Missing no-legacy-vendor-script-smoke.php\n");
    exit(1);
}

if (!vendoring_has_script($composer, 'test:no-legacy-vendor-script')) {
    fwrite(STDERR, '[FAIL] missing composer script: test:no-legacy-vendor-script' . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, '[OK] no-legacy-vendor-script smoke script and composer script are present' . PHP_EOL);

fwrite(STDOUT, '[OK] master smoke orchestration coverage is complete, including base smoke file coverage, including base smoke file coverage, including root and transaction parity tail guards' . PHP_EOL);
