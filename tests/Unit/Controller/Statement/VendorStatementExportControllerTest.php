<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Controller\Statement;

use App\Vendoring\Controller\Vendor\VendorStatementExportController;
use App\Vendoring\DTO\Api\VendorStatementWindowQueryRequestDTO;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\Service\Statement\VendorStatementRequestResolverService;
use App\Vendoring\ServiceInterface\Api\VendorStatementWindowQueryRequestResolverServiceInterface;
use App\Vendoring\Tests\Support\Statement\FakeStatementExporterPdf;
use App\Vendoring\Tests\Support\Statement\FakeVendorStatementService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorStatementExportControllerTest extends TestCase
{
    public function testExportReturnsValidationErrorWhenTenantIdIsMissing(): void
    {
        $statementService = new FakeVendorStatementService(['items' => []]);
        $windowResolver = $this->createMock(VendorStatementWindowQueryRequestResolverServiceInterface::class);
        $windowResolver->expects(self::once())
            ->method('resolve')
            ->willThrowException(VendorApiQueryValidationException::fromConstraintMessage('tenant_id_required'));

        $controller = new VendorStatementExportController(
            $statementService,
            new FakeStatementExporterPdf(sys_get_temp_dir() . '/unused-vendoring-export.pdf'),
            new VendorStatementRequestResolverService(),
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
        $windowResolver = $this->createMock(VendorStatementWindowQueryRequestResolverServiceInterface::class);
        $windowResolver->expects(self::once())
            ->method('resolve')
            ->willReturn(new VendorStatementWindowQueryRequestDTO('tenant-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD'));
        $controller = new VendorStatementExportController(
            $statementService,
            new FakeStatementExporterPdf($pdfPath),
            new VendorStatementRequestResolverService(),
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
        $windowResolver = $this->createMock(VendorStatementWindowQueryRequestResolverServiceInterface::class);
        $windowResolver->expects(self::once())
            ->method('resolve')
            ->willReturn(new VendorStatementWindowQueryRequestDTO('tenant-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD'));
        $controller = new VendorStatementExportController(
            $statementService,
            new FakeStatementExporterPdf(sys_get_temp_dir() . '/missing-vendoring-export.pdf'),
            new VendorStatementRequestResolverService(),
            $windowResolver,
        );

        $response = $controller->export('vendor-1', new Request([
            'tenantId' => 'tenant-1',
            'from' => '2026-03-01 00:00:00',
            'to' => '2026-03-31 23:59:59',
        ]));

        $payload = self::decodePayload($response);

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('statement_export_unreadable', self::payloadString($payload, 'error'));
        self::assertNotNull(self::payloadString($payload, 'hint'));
        self::assertStringContainsString('Unable to read export file at path:', self::payloadString($payload, 'hint'));
    }

    /** @return array<string, mixed> */
    private static function decodePayload(JsonResponse $response): array
    {
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        /** @var array<string, mixed> $payload */
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

        /** @var array<string, mixed> $data */
        return $data;
    }
    /**
     * @param array<string, mixed> $payload
     */
    private static function payloadString(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;

        return is_string($value) ? $value : null;
    }

}
