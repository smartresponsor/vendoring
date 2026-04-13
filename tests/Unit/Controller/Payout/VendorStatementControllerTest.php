<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Controller\Payout;

use App\Controller\Payout\VendorStatementController;
use App\DTO\Api\StatementWindowQueryRequestDTO;
use App\Exception\ApiQueryValidationException;
use App\Service\Statement\VendorStatementRequestResolver;
use App\ServiceInterface\Api\StatementWindowQueryRequestResolverInterface;
use App\Tests\Support\Statement\FakeVendorStatementService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class VendorStatementControllerTest extends TestCase
{
    public function testBuildReturnsValidationErrorWhenParamsMissing(): void
    {
        $windowResolver = $this->createMock(StatementWindowQueryRequestResolverInterface::class);
        $windowResolver->expects(self::once())
            ->method('resolve')
            ->willThrowException(ApiQueryValidationException::fromConstraintMessage('statement_from_required'));
        $controller = new VendorStatementController(new FakeVendorStatementService(['items' => []]), new VendorStatementRequestResolver(), $windowResolver);

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

        $windowResolver = $this->createMock(StatementWindowQueryRequestResolverInterface::class);
        $windowResolver->expects(self::once())
            ->method('resolve')
            ->willReturn(new StatementWindowQueryRequestDTO('tenant-1', '2026-03-01', '2026-03-31', 'USD'));
        $controller = new VendorStatementController($service, new VendorStatementRequestResolver(), $windowResolver);
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

        if (!is_array($payload)) {
            self::fail('Expected array payload.');
        }

        return $payload;
    }

    private static function payloadString(mixed $payload, string $key): ?string
    {
        if (!is_array($payload)) {
            self::fail('Expected array payload.');
        }

        $value = $payload[$key] ?? null;

        return is_string($value) ? $value : null;
    }

}
