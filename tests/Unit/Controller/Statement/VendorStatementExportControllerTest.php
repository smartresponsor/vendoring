<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Statement;

use App\Controller\Statement\VendorStatementExportController;
use App\Tests\Support\Statement\FakeStatementExporterPDF;
use App\Tests\Support\Statement\FakeVendorStatementService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class VendorStatementExportControllerTest extends TestCase
{
    public function testExportReturnsBase64PayloadWhenPdfWasGenerated(): void
    {
        $pdfPath = sys_get_temp_dir().'/vendoring-export-test.pdf';
        file_put_contents($pdfPath, 'pdf-binary');

        $statementService = new FakeVendorStatementService([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'opening' => 0.0,
            'earnings' => 10.0,
            'refunds' => 0.0,
            'fees' => 0.0,
            'closing' => 10.0,
            'items' => [],
        ]);
        $controller = new VendorStatementExportController($statementService, new FakeStatementExporterPDF($pdfPath));

        $response = $controller->export('vendor-1', new Request([
            'tenantId' => 'tenant-1',
            'from' => '2026-03-01 00:00:00',
            'to' => '2026-03-31 23:59:59',
            'currency' => 'USD',
        ]));

        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        @unlink($pdfPath);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(base64_encode('pdf-binary'), $payload['data']['pdfBase64']);
        self::assertSame($pdfPath, $payload['data']['path']);
        self::assertCount(1, $statementService->requests());
    }

    public function testExportReturnsServerErrorWhenPdfPathIsUnreadable(): void
    {
        $statementService = new FakeVendorStatementService([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'opening' => 0.0,
            'earnings' => 10.0,
            'refunds' => 0.0,
            'fees' => 0.0,
            'closing' => 10.0,
            'items' => [],
        ]);
        $controller = new VendorStatementExportController($statementService, new FakeStatementExporterPDF(sys_get_temp_dir().'/missing-vendoring-export.pdf'));

        $response = $controller->export('vendor-1', new Request([
            'tenantId' => 'tenant-1',
            'from' => '2026-03-01 00:00:00',
            'to' => '2026-03-31 23:59:59',
        ]));

        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('statement_export_unreadable', $payload['error']);
        self::assertSame('vendor-1', $payload['data']['vendorId']);
    }
}
