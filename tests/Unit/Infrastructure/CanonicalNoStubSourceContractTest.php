<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalNoStubSourceContractTest extends TestCase
{
    public function testSrcDoesNotContainStubMarkers(): void
    {
        $root = dirname(__DIR__, 3);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.'/src'));

        foreach ($iterator as $file) {
            if (!$file->isFile() || 'php' !== $file->getExtension()) {
                continue;
            }

            $contents = (string) file_get_contents($file->getPathname());
            self::assertStringNotContainsStringIgnoringCase('stub', $contents, 'Forbidden stub marker remains in: '.$file->getPathname());
        }
    }
}
