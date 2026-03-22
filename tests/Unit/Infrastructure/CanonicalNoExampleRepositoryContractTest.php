<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalNoExampleRepositoryContractTest extends TestCase
{
    public function testOperationalRepositoryLayersDoNotContainExampleDomainMarkers(): void
    {
        $root = dirname(__DIR__, 3);
        $paths = [
            '.commanding',
            '.deploy',
            'ops',
            'config',
            'scripts',
            '.smoke',
            'bin',
            'public',
            'tools',
            'src',
        ];

        $hits = [];

        foreach ($paths as $path) {
            $absolutePath = $root.DIRECTORY_SEPARATOR.$path;
            if (!is_dir($absolutePath)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absolutePath, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());
                if (false === $contents) {
                    continue;
                }

                if (str_contains($contents, 'example.com')) {
                    $hits[] = str_replace($root.DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }

        self::assertSame([], $hits, 'Found example.com markers in operational repository layers: '.implode(', ', $hits));
    }
}
