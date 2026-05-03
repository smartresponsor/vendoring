<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Controller\Payout;

use App\Vendoring\Controller\Vendor\VendorStatementController;
use App\Vendoring\DTO\Api\VendorStatementWindowQueryRequestDTO;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\Service\Statement\VendorStatementRequestResolverService;
use App\Vendoring\ServiceInterface\Api\VendorStatementWindowQueryRequestResolverServiceInterface;
use App\Vendoring\Tests\Support\Statement\FakeVendorStatementService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class VendorStatementControllerTest extends TestCase
{
    public function testBuildReturnsValidationErrorWhenParamsMissing(): void
    {
        $windowResolver = $this->createMock(VendorStatementWindowQueryRequestResolverServiceInterface::class);
        $windowResolver->expects(self::once())
            ->method('resolve')
            ->willThrowException(VendorApiQueryValidationException::fromConstraintMessage('statement_from_required'));
        $controller = new VendorStatementController(new FakeVendorStatementService(['items' => []]), new VendorStatementRequestResolverService(), $windowResolver);

        $response = $controller->build('vendor-1', new Request());
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('statement_from_required', self::payloadString($payload, 'error'));
        self::assertSame('Provide the from query parameter.', self::payloadString($payload, 'hint'));
    }

    public function testBuildReturnsStatementPayload(): void
    {
        $service = new FakeVendorStatementService([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
            'closing' => 120.5,
            'items' => [
                ['type' => 'earnings', 'amount' => 120.5, 'currency' => 'USD'],
            ],
        ]);

        $windowResolver = $this->createMock(VendorStatementWindowQueryRequestResolverServiceInterface::class);
        $windowResolver->expects(self::once())
            ->method('resolve')
            ->willReturn(new VendorStatementWindowQueryRequestDTO('tenant-1', '2026-03-01', '2026-03-31', 'USD'));
        $controller = new VendorStatementController($service, new VendorStatementRequestResolverService(), $windowResolver);
        $request = new Request([
            'tenantId' => 'tenant-1',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
        ]);

        $response = $controller->build('vendor-1', $request);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('tenant-1', (string) $response->getContent());
        self::assertStringContainsString('120.5', (string) $response->getContent());
    }


    /** @return array<string, mixed> */
    private static function decodePayload(\Symfony\Component\HttpFoundation\Response $response): array
    {
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    private static function payloadString(mixed $payload, string $key): ?string
    {
        $value = is_array($payload) ? ($payload[$key] ?? null) : null;

        return is_string($value) ? $value : null;
    }

}
