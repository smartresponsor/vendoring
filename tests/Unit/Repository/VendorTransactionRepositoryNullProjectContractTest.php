<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Repository;

use PHPUnit\Framework\TestCase;

final class VendorTransactionRepositoryNullProjectContractTest extends TestCase
{
    public function testRepositoryUsesExplicitNullProjectBranch(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Repository/Vendor/VendorTransactionRepository.php');

        self::assertStringContainsString('transaction.projectId IS NULL', $source);
        self::assertStringContainsString('transaction.projectId = :projectId', $source);
    }
}
