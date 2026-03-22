<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalRootRuntimeArtifactContractTest extends TestCase
{
    public function testRepositoryVarDirectoryDoesNotContainPersistentPhpCsFixerCache(): void
    {
        $root = dirname(__DIR__, 3);

        self::assertFileDoesNotExist(
            $root.'/var/.php-cs-fixer.cache',
            'Persistent php-cs-fixer cache must not be committed into the cumulative source snapshot.'
        );
    }

    public function testRepositoryDoesNotContainCommittedOperationalActionLog(): void
    {
        $root = dirname(__DIR__, 3);

        self::assertFileDoesNotExist(
            $root.'/.commanding/logs/actions.log',
            'Committed operational action log must not be present in the cumulative source snapshot.'
        );
    }
}
