<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalRootRuntimeArtifactContractTest extends TestCase
{
    public function testRepositoryVarDirectoryPhpCsFixerCacheIsIgnoredByGit(): void
    {
        $root = dirname(__DIR__, 3);
        $gitignore = (string) file_get_contents($root.'/.gitignore');

        self::assertStringContainsString('.php-cs-fixer.cache', $gitignore, 'Local php-cs-fixer cache artifacts must be ignored by git when present in a current slice.');
    }

    public function testRepositoryOperationalActionLogIsIgnoredByGit(): void
    {
        $root = dirname(__DIR__, 3);
        $gitignore = (string) file_get_contents($root.'/.gitignore');

        self::assertStringContainsString('*.log', $gitignore, 'Operational action logs under canonical tooling dot-folders must be ignored by git when present in a current slice.');
    }
}
