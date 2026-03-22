<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalRootStructureContractTest extends TestCase
{
    public function testVendorTransactionControllerExistsOnlyInCanonicalSrcControllerLocation(): void
    {
        $root = dirname(__DIR__, 3);

        self::assertFileExists($root.'/src/Controller/VendorTransactionController.php');
        self::assertFileDoesNotExist($root.'/VendorTransactionController.php');
    }

    public function testRootLevelNonDotPhpArtifactsAreNotPresent(): void
    {
        $root = dirname(__DIR__, 3);
        $phpFiles = glob($root.'/*.php') ?: [];
        $nonDotPhpFiles = array_values(array_filter($phpFiles, static fn (string $path): bool => !str_starts_with(basename($path), '.')));

        self::assertSame([], $nonDotPhpFiles, 'Root-level non-dot PHP files are forbidden; move runtime/test files under canonical roots.');
    }

    public function testRepositoryRootDoesNotContainWaveArtifactMarkdownFiles(): void
    {
        $root = dirname(__DIR__, 3);
        $files = array_values(array_filter(scandir($root) ?: [], static function (string $entry): bool {
            if ('.' === $entry || '..' === $entry) {
                return false;
            }

            return (bool) preg_match('/^vendoring-wave\d+.*\.md$/', $entry);
        }));

        self::assertSame([], $files, 'Root must not contain wave artifact markdown files; keep such files under report/ or docs/.');
    }

    public function testRepositoryRootDoesNotContainProtocolAnalysisMarkdownFiles(): void
    {
        $root = dirname(__DIR__, 3);
        $files = array_values(array_filter(scandir($root) ?: [], static function (string $entry): bool {
            if ('.' === $entry || '..' === $entry) {
                return false;
            }

            return (bool) preg_match('/^[A-Z0-9_-]+_PROTOCOL_ANALYSIS\.md$/', $entry);
        }));

        self::assertSame([], $files, 'Root must not contain protocol analysis markdown files; keep such files under report/ or docs/.');
    }

    public function testRepositoryRootDoesNotContainPersistentVendorDirectory(): void
    {
        $root = dirname(__DIR__, 3);

        self::assertDirectoryDoesNotExist($root.'/vendor', 'Persistent vendor/ directory must not be committed into the cumulative source snapshot.');
    }
}
