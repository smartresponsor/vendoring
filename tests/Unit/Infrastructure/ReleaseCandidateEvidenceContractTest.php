<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class ReleaseCandidateEvidenceContractTest extends TestCase
{
    public function testComposerDefinesReleaseCandidateEvidenceScripts(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 3).'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        $scripts = $composer['scripts'] ?? [];

        foreach (['docs:rc-evidence', 'test:rc-evidence'] as $script) {
            self::assertArrayHasKey($script, $scripts, 'Missing RC evidence script: '.$script);
        }
    }

    public function testReleaseCandidateEvidenceFilesExist(): void
    {
        $root = dirname(__DIR__, 3);

        self::assertFileExists($root.'/bin/generate-rc-evidence.php');
        self::assertFileExists($root.'/docs/release/RC_EVIDENCE_PACK.md');
    }

    public function testReleaseCandidateWorkflowPublishesReleaseArtifactsAndUsesSqliteCapablePhp(): void
    {
        $workflow = (string) file_get_contents(dirname(__DIR__, 3).'/.github/workflows/release-candidate.yml');

        self::assertStringContainsString('pdo_sqlite', $workflow);
        self::assertStringContainsString('Upload RC evidence artifacts', $workflow);
        self::assertStringContainsString('build/release/**', $workflow);
    }

    public function testRuntimeWorkflowUsesSqliteCapablePhpForVerticalSliceProofs(): void
    {
        $workflow = (string) file_get_contents(dirname(__DIR__, 3).'/.github/workflows/runtime.yml');

        self::assertStringContainsString('pdo_sqlite', $workflow);
        self::assertStringContainsString('build/release/**', $workflow);
    }
}
