<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Controller\Statement;

use App\Controller\Statement\VendorStatementExportController;
use App\DTO\Api\StatementWindowQueryRequestDTO;
use App\Service\Statement\VendorStatementRequestResolver;
use App\ServiceInterface\Api\StatementWindowQueryRequestResolverInterface;
use App\Tests\Support\Statement\FakeStatementExporterPDF;
use App\Tests\Support\Statement\FakeVendorStatementService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorStatementExportControllerTest extends TestCase
{
    public function testExportReturnsValidationErrorWhenTenantIdIsMissing(): void
    {
        $statementService = new FakeVendorStatementService(['items' => []]);
        $windowResolver = $this->createMock(StatementWindowQueryRequestResolverInterface::class);
        $windowResolver->expects(self::once())
            ->method('resolve')
            ->willThrowException(new \InvalidArgumentException('tenant_id_required'));

        $controller = new VendorStatementExportController(
            $statementService,
            new FakeStatementExporterPDF(sys_get_temp_dir() . '/unused-vendoring-export.pdf'),
            new VendorStatementRequestResolver(),
            $windowResolver,
        );

        $response = $controller->export('vendor-1', new Request());
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('tenant_id_required', $payload['error'] ?? null);
        self::assertSame('Provide the tenantId query parameter.', $payload['hint'] ?? null);
    }

    public function testExportReturnsBase64PayloadWhenPdfWasGenerated(): void
    {
        $pdfPath = sys_get_temp_dir() . '/vendoring-export-test.pdf';
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
        $windowResolver = $this->createMock(StatementWindowQueryRequestResolverInterface::class);
        $windowResolver->expects(self::once())
            ->method('resolve')
            ->willReturn(new StatementWindowQueryRequestDTO('tenant-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD'));
        $controller = new VendorStatementExportController(
            $statementService,
            new FakeStatementExporterPDF($pdfPath),
            new VendorStatementRequestResolver(),
            $windowResolver,
        );

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
        $windowResolver = $this->createMock(StatementWindowQueryRequestResolverInterface::class);
        $windowResolver->expects(self::once())
            ->method('resolve')
            ->willReturn(new StatementWindowQueryRequestDTO('tenant-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD'));
        $controller = new VendorStatementExportController(
            $statementService,
            new FakeStatementExporterPDF(sys_get_temp_dir() . '/missing-vendoring-export.pdf'),
            new VendorStatementRequestResolver(),
            $windowResolver,
        );

        $response = $controller->export('vendor-1', new Request([
            'tenantId' => 'tenant-1',
            'from' => '2026-03-01 00:00:00',
            'to' => '2026-03-31 23:59:59',
        ]));

        $payload = self::decodePayload($response);

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('statement_export_unreadable', $payload['error'] ?? null);
        self::assertStringContainsString('Unable to read export file at path:', (string) ($payload['hint'] ?? ''));
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
