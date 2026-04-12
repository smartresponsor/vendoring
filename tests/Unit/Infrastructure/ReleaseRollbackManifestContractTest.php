<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/bin/_composer_json.php';

final class ReleaseRollbackManifestContractTest extends TestCase
{
    public function testComposerDefinesReleaseManifestScripts(): void
    {
        $composer = vendoring_load_composer_json(dirname(__DIR__, 3));
        $scripts = vendoring_composer_section($composer, 'scripts');

        foreach (['docs:release-manifest', 'test:release-rollback-manifest'] as $script) {
            self::assertArrayHasKey($script, $scripts, 'Missing release manifest script: ' . $script);
        }
    }

    public function testReleaseRollbackManifestFilesExist(): void
    {
        $root = dirname(__DIR__, 3);

        self::assertFileExists($root . '/bin/generate-release-manifest.php');
        self::assertFileExists($root . '/docs/release/RC_RELEASE_MANIFEST.md');
        self::assertFileExists($root . '/docs/release/RC_ROLLBACK_MANIFEST.md');
        self::assertFileExists($root . '/tests/bin/release-rollback-manifest-contract-smoke.php');
    }
}
