<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalRootRemovedFilesContractTest extends TestCase
{
    public function testRemovedFilesManifestDoesNotPersistInRepositoryRoot(): void
    {
        $root = dirname(__DIR__, 3);
        self::assertFileDoesNotExist(
            $root . '/REMOVED_FILES.txt',
            'REMOVED_FILES.txt must not persist in the repository root of the cumulative snapshot.',
        );
    }
}
