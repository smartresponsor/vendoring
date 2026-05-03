<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

require_once dirname(__DIR__, 2) . '/bin/_composer_json.php';

use PHPUnit\Framework\TestCase;

final class ComposerRootGuardScriptParityTest extends TestCase
{
    public function testRootGuardScriptsUseCanonicalSmokeAndUnitFilterPattern(): void
    {
        $composer = vendoring_load_composer_json(dirname(__DIR__, 3));
        $scripts = vendoring_composer_section($composer, 'scripts');

        $expected = [
            'test:root-structure' => 'CanonicalRootStructureContractTest',
            'test:root-protocol-cleanup' => 'CanonicalRootStructureContractTest',
            'test:root-vendor-cleanup' => 'CanonicalRootStructureContractTest',
            'test:root-removed-files' => 'CanonicalRootRemovedFilesContractTest',
            'test:root-runtime-artifacts' => 'CanonicalRootRuntimeArtifactContractTest',
            'test:idea-runtime-artifact' => 'CanonicalIdeRuntimeArtifactContractTest',
            'test:idea-module-artifact' => 'CanonicalIdeRuntimeArtifactContractTest',
        ];

        foreach ($expected as $scriptName => $filter) {
            self::assertArrayHasKey($scriptName, $scripts);
            self::assertIsArray($scripts[$scriptName], $scriptName . ' must be an array script.');
            self::assertCount(2, $scripts[$scriptName], $scriptName . ' must have smoke + phpunit.');
            self::assertSame('php tests/bin/' . self::smokeScriptName($scriptName), $scripts[$scriptName][0]);
            self::assertSame(
                'php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite unit --filter ' . $filter,
                $scripts[$scriptName][1],
            );
        }
    }

    private static function smokeScriptName(string $scriptName): string
    {
        return match ($scriptName) {
            'test:root-structure', 'test:root-protocol-cleanup', 'test:root-vendor-cleanup' => 'root-structure-smoke.php',
            'test:root-removed-files' => 'root-removed-files-smoke.php',
            'test:root-runtime-artifacts' => 'root-runtime-artifact-smoke.php',
            'test:idea-runtime-artifact' => 'idea-runtime-artifact-smoke.php',
            'test:idea-module-artifact' => 'idea-module-artifact-smoke.php',
            default => throw new \LogicException('Unsupported script ' . $scriptName),
        };
    }
}
