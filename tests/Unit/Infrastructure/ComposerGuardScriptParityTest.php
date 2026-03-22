<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class ComposerGuardScriptParityTest extends TestCase
{
    public function testNoStubAndPlaceholderGuardScriptsUseCanonicalSmokePlusUnitFilterPattern(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 3).'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);

        $expected = [
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
        ];

        foreach ($expected as $scriptName => $commands) {
            self::assertSame($commands, $composer['scripts'][$scriptName] ?? null, $scriptName.' must use canonical smoke + unit/filter pattern');
        }
    }

    public function testQualityPipelineIncludesComposerGuardParitySlice(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 3).'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertContains('@test:composer-guard-parity', $composer['scripts']['quality'] ?? []);
    }
}
