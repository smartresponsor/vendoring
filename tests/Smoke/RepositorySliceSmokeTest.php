<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use PHPUnit\Framework\TestCase;

final class RepositorySliceSmokeTest extends TestCase
{
    public function testCurrentSliceProvidesSourceAndTestsDirectories(): void
    {
        self::assertDirectoryExists(dirname(__DIR__, 2) . '/src');
        self::assertDirectoryExists(dirname(__DIR__));
    }

    public function testComposerJsonExistsInRepositoryRoot(): void
    {
        self::assertFileExists(dirname(__DIR__, 2) . '/composer.json');
    }
}
