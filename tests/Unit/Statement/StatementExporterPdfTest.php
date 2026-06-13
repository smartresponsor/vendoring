<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Statement;

use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\Service\Statement\VendorStatementExporterPdfService;
use PHPUnit\Framework\TestCase;

final class StatementExporterPdfTest extends TestCase
{
    public function testExportCreatesPdfInExpectedVendorPeriodPath(): void
    {
        $dto = new VendorStatementRequestDTO('tenant-1', 'vendor-42', '2026-03-01', '2026-03-31', 'USD');
        $data = [
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-42',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
            'opening' => 10.0,
            'earnings' => 20.0,
            'refunds' => 3.0,
            'fees' => 2.0,
            'closing' => 25.0,
            'items' => [
                ['type' => 'earnings', 'amount' => 20.0, 'currency' => 'USD'],
            ],
        ];

        $path = (new VendorStatementExporterPdfService())->export($dto, $data, null);

        self::assertStringEndsWith('var/statements/2026/03/vendor-42/statement_20260301_20260331.pdf', $path);
        self::assertFileExists($path);

        $content = file_get_contents($path);
        self::assertNotFalse($content);
        self::assertStringStartsWith('%PDF-1.4', $content);

        $this->cleanupGeneratedFile($path);
    }

    public function testExportEmbedsStatementValuesIntoPdfStream(): void
    {
        $dto = new VendorStatementRequestDTO('tenant-a', 'vendor-a', '2026-04-01', '2026-04-30', 'EUR');
        $data = [
            'tenantId' => 'tenant-a',
            'vendorId' => 'vendor-a',
            'from' => '2026-04-01',
            'to' => '2026-04-30',
            'currency' => 'EUR',
            'opening' => 100.5,
            'earnings' => 200.25,
            'refunds' => 50.0,
            'fees' => 10.75,
            'closing' => 240.0,
            'items' => [
                ['type' => 'fees', 'amount' => 10.75, 'currency' => 'EUR'],
            ],
        ];

        $path = (new VendorStatementExporterPdfService())->export($dto, $data, null);
        $content = file_get_contents($path);
        self::assertNotFalse($content);

        self::assertStringContainsString('Tenant: tenant-a', $content);
        self::assertStringContainsString('Vendor: vendor-a', $content);
        self::assertStringContainsString('Currency: EUR', $content);
        self::assertStringContainsString('Opening: 100.50', $content);
        self::assertStringContainsString('Closing: 240.00', $content);

        $this->cleanupGeneratedFile($path);
    }

    private function cleanupGeneratedFile(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        }

        $vendorDir = dirname($path);
        $monthDir = dirname($vendorDir);
        $yearDir = dirname($monthDir);

        @rmdir($vendorDir);
        @rmdir($monthDir);
        @rmdir($yearDir);
    }
}
