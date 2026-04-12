<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalIdeRuntimeArtifactContractTest extends TestCase
{
    public function testIdeaRuntimeArtifactsAreIgnoredByGit(): void
    {
        $root = dirname(__DIR__, 3);
        $gitignore = (string) file_get_contents($root . '/.gitignore');

        self::assertStringContainsString('.idea/', $gitignore, 'IDE project directory must be ignored by git when present in a local current slice.');
        self::assertStringContainsString('*.iml', $gitignore, 'IDE module files must be ignored by git when present in a local current slice.');
    }
}
