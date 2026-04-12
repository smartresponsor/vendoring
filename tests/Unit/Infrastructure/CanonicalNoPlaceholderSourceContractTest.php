<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

require_once dirname(__DIR__, 2) . '/bin/_composer_json.php';

use PHPUnit\Framework\TestCase;

final class CanonicalNoPlaceholderSourceContractTest extends TestCase
{
    public function testProductionSourceDoesNotContainPlaceholderMarkers(): void
    {
        $root = dirname(__DIR__, 3) . '/src';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));

        foreach (vendoring_php_files($iterator) as $file) {
            $path = $file->getPathname();
            $contents = (string) file_get_contents($path);
            self::assertStringNotContainsStringIgnoringCase('placeholder', $contents, $path);
        }
    }
}
