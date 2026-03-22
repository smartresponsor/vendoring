<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalIdeRuntimeArtifactContractTest extends TestCase
{
    public function testIdeaRuntimeArtifactsAreNotCommitted(): void
    {
        $root = dirname(__DIR__, 3);

        foreach ([
            '/.idea/Canonization.iml',
            '/.idea/Vendor.iml',
            '/.idea/workspace.xml',
            '/.ide/sr_default_inspector.xml',
        ] as $relative) {
            self::assertFileDoesNotExist($root.$relative, 'Forbidden IDE runtime artifact remains: '.ltrim($relative, '/'));
        }

        self::assertFalse(is_dir($root.'/.ide'), 'Forbidden empty hidden IDE directory remains: .ide');
    }
}
