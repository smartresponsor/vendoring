<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalNoExampleWordingRepositoryContractTest extends TestCase
{
    public function testOperationalRepositoryLayersDoNotContainExampleOnlyMarkers(): void
    {
        $root = dirname(__DIR__, 3);
        $files = [
            'tools/vendoring-missing-class-scan-v2.php',
        ];

        $hits = [];

        foreach ($files as $file) {
            $absolutePath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
            if (!is_file($absolutePath)) {
                continue;
            }

            $contents = file_get_contents($absolutePath);
            if (false === $contents) {
                continue;
            }

            if (str_contains($contents, 'example only') || str_contains($contents, 'example: canonization') || str_contains($contents, 'Examples:')) {
                $hits[] = $file;
            }
        }

        self::assertSame([], $hits, 'Found repository-level example wording markers: ' . implode(', ', $hits));
    }
}
