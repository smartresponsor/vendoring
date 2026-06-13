<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

require_once dirname(__DIR__, 2) . '/bin/_composer_json.php';

use PHPUnit\Framework\TestCase;

final class CanonicalNoStubSourceContractTest extends TestCase
{
    public function testSrcDoesNotContainStubMarkers(): void
    {
        $root = dirname(__DIR__, 3);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/src', \FilesystemIterator::SKIP_DOTS));

        foreach (vendoring_php_files($iterator) as $file) {
            $contents = (string) file_get_contents($file->getPathname());
            self::assertStringNotContainsStringIgnoringCase('stub', $contents, 'Forbidden stub marker remains in: ' . $file->getPathname());
        }
    }
}
