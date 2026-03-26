<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Controller\Statement;

use App\Controller\Statement\VendorStatementExportController;
use App\Tests\Support\Statement\FakeStatementExporterPDF;
use App\Tests\Support\Statement\FakeVendorStatementService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        $payload = self::decodePayload($response);
        $data = self::payloadData($payload);

        if (is_file($pdfPath) && !unlink($pdfPath)) {
            self::fail(sprintf('Failed to delete temp pdf file: %s', $pdfPath));
        }

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(base64_encode('pdf-binary'), $data['pdfBase64'] ?? null);
        self::assertSame($pdfPath, $data['path'] ?? null);
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

        $payload = self::decodePayload($response);
        $data = self::payloadData($payload);

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('statement_export_unreadable', $payload['error'] ?? null);
        self::assertSame('vendor-1', $data['vendorId'] ?? null);
    }

    /** @return array<string, mixed> */
    private static function decodePayload(JsonResponse $response): array
    {
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($payload)) {
            self::fail('Expected array payload.');
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private static function payloadData(array $payload): array
    {
        $data = $payload['data'] ?? null;

        if (!is_array($data)) {
            self::fail('Expected array data payload.');
        }

        return $data;
    }
}
