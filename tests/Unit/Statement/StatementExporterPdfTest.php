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

        self::assertStringContainsString('var/statements/2026/03/'.hash('sha256', 'tenant-1').'/'.hash('sha256', 'vendor-42').'/statement_20260301_20260331_', $path);
        self::assertStringEndsWith('.pdf', $path);
        self::assertFileExists($path);

        $content = file_get_contents($path);
        self::assertNotFalse($content);
        self::assertStringStartsWith('%PDF-1.4', $content);

        $this->cleanupGeneratedFile($path);
    }

    public function testExportEmbedsStatementValuesIntoPdfStream(): void
    {
        $dto = new VendorStatementRequestDTO('tenant-1', 'vendor-a', '2026-04-01', '2026-04-30', 'EUR');
        $data = [
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

        self::assertStringContainsString('Tenant: tenant-1', $content);
        self::assertStringContainsString('Vendor: vendor-a', $content);
        self::assertStringContainsString('Currency: EUR', $content);
        self::assertStringContainsString('Opening: 100.50', $content);
        self::assertStringContainsString('Closing: 240.00', $content);

        $this->cleanupGeneratedFile($path);
    }

    public function testExportsAreIsolatedAcrossTenantsAndConcurrentRequests(): void
    {
        $exporter = new VendorStatementExporterPdfService();
        $dtoA = new VendorStatementRequestDTO('tenant-a', '../vendor', '2026-03-01', '2026-03-31', 'USD');
        $dtoB = new VendorStatementRequestDTO('tenant-b', '../vendor', '2026-03-01', '2026-03-31', 'USD');
        $data = ['vendorId' => '../vendor', 'from' => $dtoA->from, 'to' => $dtoA->to, 'currency' => 'USD', 'opening' => 0.0, 'earnings' => 1.0, 'refunds' => 0.0, 'fees' => 0.0, 'closing' => 1.0, 'items' => []];
        $a = $exporter->export($dtoA, $data);
        $b = $exporter->export($dtoB, [...$data, 'earnings' => 200.0]);
        $again = $exporter->export($dtoA, $data);
        try {
            self::assertNotSame(dirname($a), dirname($b));
            self::assertNotSame($a, $again);
            self::assertStringNotContainsString('..', $a);
            self::assertStringContainsString('Tenant: tenant-a', (string) file_get_contents($a));
            self::assertStringContainsString('Tenant: tenant-b', (string) file_get_contents($b));
            self::assertStringNotContainsString('Tenant: tenant-b', (string) file_get_contents($a));
            self::assertStringContainsString('Earnings: 1.00', (string) file_get_contents($a));
            self::assertStringContainsString('Earnings: 200.00', (string) file_get_contents($b));
        } finally {
            foreach ([$a, $b, $again] as $path) {
                $this->cleanupGeneratedFile($path);
            }
        }
    }

    private function cleanupGeneratedFile(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        }

        $vendorDir = dirname($path);
        $tenantDir = dirname($vendorDir);
        $monthDir = dirname($tenantDir);
        $yearDir = dirname($monthDir);

        @rmdir($vendorDir);
        @rmdir($tenantDir);
        @rmdir($monthDir);
        @rmdir($yearDir);
    }
}
