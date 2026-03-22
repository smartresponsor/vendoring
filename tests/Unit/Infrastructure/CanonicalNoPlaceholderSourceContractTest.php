<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalNoPlaceholderSourceContractTest extends TestCase
{
    public function testProductionSourceDoesNotContainPlaceholderMarkers(): void
    {
        $root = dirname(__DIR__, 3).'/src';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));

        foreach ($iterator as $file) {
            if (!$file->isFile() || 'php' !== $file->getExtension()) {
                continue;
            }

            $path = $file->getPathname();
            $contents = (string) file_get_contents($path);

            self::assertStringNotContainsStringIgnoringCase('placeholder', $contents, $path);
        }
    }
}
