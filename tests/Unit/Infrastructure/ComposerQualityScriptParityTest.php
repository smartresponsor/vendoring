<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class ComposerQualityScriptParityTest extends TestCase
{
    public function testQualityScriptUsesCanonicalAtReferencesForTestCommands(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 3).'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        $quality = $composer['scripts']['quality'] ?? null;

        self::assertIsArray($quality, 'quality script must be an array.');

        foreach ($quality as $entry) {
            self::assertIsString($entry, 'quality entries must be strings.');
            self::assertStringNotContainsString('composer test:', $entry, 'quality must not shell-call composer test:* directly.');
            self::assertStringNotContainsString('&&', $entry, 'quality must not chain test scripts inline.');
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
            self::assertContains($scriptName, $quality, 'Missing canonical quality entry: '.$scriptName);
        }
    }
}
