<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Controller\Statement;

use App\Controller\Statement\VendorStatementDeliveryRuntimeController;
use App\DTO\Api\StatementWindowQueryRequestDTO;
use App\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Exception\ApiQueryValidationException;
use App\Projection\VendorStatementDeliveryRuntimeView;
use App\Service\Statement\VendorStatementRequestResolver;
use App\ServiceInterface\Api\StatementWindowQueryRequestResolverInterface;
use App\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class VendorStatementDeliveryRuntimeControllerTest extends TestCase
{
    private VendorStatementDeliveryRuntimeViewBuilderInterface&MockObject $builder;
    private StatementWindowQueryRequestResolverInterface&MockObject $statementWindowQueryRequestResolver;

    protected function setUp(): void
    {
        $this->builder = $this->createMock(VendorStatementDeliveryRuntimeViewBuilderInterface::class);
        $this->statementWindowQueryRequestResolver = $this->createMock(StatementWindowQueryRequestResolverInterface::class);
    }

    public function testShowReturnsValidationErrorWhenParamsMissing(): void
    {
        $this->statementWindowQueryRequestResolver->expects(self::once())
            ->method('resolve')
            ->willThrowException(ApiQueryValidationException::fromConstraintMessage('statement_to_required'));
        $controller = new VendorStatementDeliveryRuntimeController(
            $this->builder,
            new VendorStatementRequestResolver(),
            $this->statementWindowQueryRequestResolver,
        );

        $response = $controller->show('vendor-1', new Request());
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('statement_to_required', self::payloadString($payload, 'error'));
        self::assertSame('Provide the to query parameter.', self::payloadString($payload, 'hint'));
    }

    public function testShowBuildsRuntimeViewFromResolvedRequest(): void
    {
        $this->statementWindowQueryRequestResolver->expects(self::once())
            ->method('resolve')
            ->willReturn(new StatementWindowQueryRequestDTO('tenant-1', '2026-03-01', '2026-03-31', 'USD'));
        $this->builder->expects(self::once())
            ->method('build')
            ->with(self::callback(function (VendorStatementDeliveryRuntimeRequestDTO $request): bool {
                return 'tenant-1' === $request->tenantId
                    && 'vendor-1' === $request->vendorId
                    && '2026-03-01' === $request->from
                    && '2026-03-31' === $request->to
                    && 'USD' === $request->currency
                    && false === $request->includeExport;
            }))
            ->willReturn(new VendorStatementDeliveryRuntimeView(
                tenantId: 'tenant-1',
                vendorId: 'vendor-1',
                currency: 'USD',
                ownership: null,
                statement: ['closing' => 10.0],
                export: null,
                recipients: [],
            ));

        $controller = new VendorStatementDeliveryRuntimeController(
            $this->builder,
            new VendorStatementRequestResolver(),
            $this->statementWindowQueryRequestResolver,
        );
        $response = $controller->show('vendor-1', new Request([
            'tenantId' => 'tenant-1',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
            'includeExport' => 'false',
        ]));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('tenant-1', (string) $response->getContent());
        self::assertStringContainsString('closing', (string) $response->getContent());
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
