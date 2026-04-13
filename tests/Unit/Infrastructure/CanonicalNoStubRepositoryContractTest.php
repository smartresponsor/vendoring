<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalNoStubRepositoryContractTest extends TestCase
{
    public function testRepositoryDoesNotContainStubMarkersOutsideAllowedTrees(): void
    {
        $root = dirname(__DIR__, 3);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        $violations = [];

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $path = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));

            if ($this->isIgnoredPath($path)) {
                continue;
            }

            $content = (string) file_get_contents($file->getPathname());
            if (1 === preg_match('/\bstubs?\b/i', $content)) {
                $violations[] = $path;
            }
        }

        self::assertSame([], $violations, 'Repository contains forbidden stub markers: ' . implode(', ', $violations));
    }

    private function isIgnoredPath(string $path): bool
    {
        foreach ([
            'report/',
            'tests/',
            'docs/',
            '.idea/',
            '.git',
            '.release/',
            'vendor/',
            '.deploy/_template/',
            '.deploy/systemd/',
            '.deploy/',
            '.github/workflows/consuming.yml',
            '.consuming/',
            'tools/report/VendorConfigGuardReport.php',
            '.php-cs-fixer.cache',
            'var/.php-cs-fixer.cache',
            'composer.json',
            'composer.lock',
        ] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return 1 === preg_match('/\.(zip|tar|gz|tgz)$/i', $path);
    }
}
