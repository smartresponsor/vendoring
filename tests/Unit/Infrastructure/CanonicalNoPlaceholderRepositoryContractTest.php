<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalNoPlaceholderRepositoryContractTest extends TestCase
{
    public function testRepositoryStateDoesNotContainForbiddenPlaceholderMarkersOutsideAllowedAreas(): void
    {
        $root = dirname(__DIR__, 3);
        $allowedPrefixes = [
            $root . '/report/',
            $root . '/tests/',
            $root . '/.idea/',
            $root . '/.deploy/_template/',
            $root . '/.phpunit.cache/',
            $root . '/build/docs/phpdocumentor/',
            $root . '/docs/',
            $root . '/docs/release/',
            $root . '/var/',
            $root . '/.deploy/systemd/',
            $root . '/.consuming/',
        ];
        $allowedFiles = [
            $root . '/composer.json',
            $root . '/package-lock.json',
            $root . '/bin/generate-phpdocumentor-site.php',
            $root . '/bin/generate-rc-evidence.php',
            $root . '/config/reference.php',
            $root . '/.php-cs-fixer.cache',
        ];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $path = $file->getPathname();
            $normalized = str_replace('\\', '/', $path);

            if (
                str_contains($normalized, '/.git/')
                || str_contains($normalized, '/vendor/')
                || str_contains($normalized, '/node_modules/')
                || str_contains($normalized, '/.deploy/_template/')
            ) {
                continue;
            }

            if (in_array($normalized, array_map(static fn(string $item): string => str_replace('\\', '/', $item), $allowedFiles), true)) {
                continue;
            }

            foreach ($allowedPrefixes as $prefix) {
                if (str_starts_with($normalized, str_replace('\\', '/', $prefix))) {
                    continue 2;
                }
            }

            $contents = file_get_contents($path);
            self::assertNotFalse($contents, $path);
            self::assertStringNotContainsStringIgnoringCase('placeholder', $contents, $path);
        }
    }
}
