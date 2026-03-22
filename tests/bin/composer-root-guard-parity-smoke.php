<?php

declare(strict_types=1);

$composer = json_decode((string) file_get_contents(__DIR__.'/../../composer.json'), true, 512, JSON_THROW_ON_ERROR);
$scripts = $composer['scripts'] ?? [];

$expected = [
    'test:root-structure' => [
        'php tests/bin/root-structure-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalRootStructureContractTest',
    ],
    'test:root-protocol-cleanup' => [
        'php tests/bin/root-structure-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalRootStructureContractTest',
    ],
    'test:root-vendor-cleanup' => [
        'php tests/bin/root-structure-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalRootStructureContractTest',
    ],
    'test:root-removed-files' => [
        'php tests/bin/root-removed-files-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalRootRemovedFilesContractTest',
    ],
    'test:root-runtime-artifacts' => [
        'php tests/bin/root-runtime-artifact-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalRootRuntimeArtifactContractTest',
    ],
    'test:idea-runtime-artifact' => [
        'php tests/bin/idea-runtime-artifact-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalIdeRuntimeArtifactContractTest',
    ],
    'test:idea-module-artifact' => [
        'php tests/bin/idea-module-artifact-smoke.php',
        'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter CanonicalIdeRuntimeArtifactContractTest',
    ],
];

foreach ($expected as $scriptName => $commands) {
    if (!array_key_exists($scriptName, $scripts)) {
        fwrite(STDERR, 'Missing composer script: '.$scriptName.PHP_EOL);
        exit(1);
    }
    if (!is_array($scripts[$scriptName])) {
        fwrite(STDERR, 'Composer root guard script must be an array: '.$scriptName.PHP_EOL);
        exit(1);
    }
    if ($scripts[$scriptName] !== $commands) {
        fwrite(STDERR, 'Composer root guard script mismatch: '.$scriptName.PHP_EOL);
        exit(1);
    }
}

echo "Composer root guard parity OK\n";
