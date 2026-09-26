<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Ops;

use App\Vendoring\Builder\Ops\VendorRuntimeStatusProjectionBuilder;
use App\Vendoring\BuilderInterface\Finance\VendorFinanceRuntimeProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Ownership\VendorOwnershipProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Statement\VendorStatementDeliveryRuntimeProjectionBuilderInterface;
use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\Projection\VendorExternalIntegrationRuntimeProjection;
use App\Vendoring\Projection\VendorFinanceRuntimeProjection;
use App\Vendoring\Projection\VendorOwnershipProjection;
use App\Vendoring\Projection\VendorStatementDeliveryRuntimeProjection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeStatusProjectionBuilderTest extends TestCase
{
    private VendorOwnershipProjectionBuilderInterface&MockObject $ownershipProjectionBuilder;
    private VendorFinanceRuntimeProjectionBuilderInterface&MockObject $financeRuntimeProjectionBuilder;
    private VendorStatementDeliveryRuntimeProjectionBuilderInterface&MockObject $statementDeliveryRuntimeProjectionBuilder;
    private VendorExternalIntegrationRuntimeProjectionBuilderInterface&MockObject $externalIntegrationRuntimeProjectionBuilder;

    protected function setUp(): void
    {
        $this->ownershipProjectionBuilder = $this->createMock(VendorOwnershipProjectionBuilderInterface::class);
        $this->financeRuntimeProjectionBuilder = $this->createMock(VendorFinanceRuntimeProjectionBuilderInterface::class);
        $this->statementDeliveryRuntimeProjectionBuilder = $this->createMock(VendorStatementDeliveryRuntimeProjectionBuilderInterface::class);
        $this->externalIntegrationRuntimeProjectionBuilder = $this->createMock(VendorExternalIntegrationRuntimeProjectionBuilderInterface::class);
    }

    public function testBuildIncludesOwnershipSurfaceForNumericVendorId(): void
    {
        $this->ownershipProjectionBuilder
            ->expects(self::once())
            ->method('buildForVendorId')
            ->with(42)
            ->willReturn(new VendorOwnershipProjection(42, 7, []));

        $this->financeRuntimeProjectionBuilder
            ->expects(self::once())
            ->method('build')
            ->with('42', '2025-01-01', '2025-01-31', 'USD')
            ->willReturn(new VendorFinanceRuntimeProjection('42', 'USD', ['ownerUserId' => 7], ['gmv' => 1000], ['provider' => 'bank'], ['closing' => 900]));

        $this->statementDeliveryRuntimeProjectionBuilder
            ->expects(self::once())
            ->method('build')
            ->with(self::callback(function (VendorStatementDeliveryRuntimeRequestDTO $request): bool {
                self::assertSame('42', $request->vendorId);
                self::assertSame('2025-01-01', $request->from);
                self::assertSame('2025-01-31', $request->to);
                self::assertSame('USD', $request->currency);

                return true;
            }))
            ->willReturn(new VendorStatementDeliveryRuntimeProjection('42', 'USD', ['ownerUserId' => 7], ['closing' => 900], ['path' => '/tmp/statement.csv'], [['email' => 'ops@example.com']]));

        $this->externalIntegrationRuntimeProjectionBuilder
            ->expects(self::once())
            ->method('build')
            ->with('42')
            ->willReturn(new VendorExternalIntegrationRuntimeProjection('42', ['ownerUserId' => 7], ['crm' => 'hubspot'], ['webhook' => 'ok'], ['payoutProvider' => 'bank'], ['crm', 'webhooks']));

        $payload = $this->buildService()->build('42', '2025-01-01', '2025-01-31', 'USD')->toArray();
        $ownership = self::assertArrayPayload($payload['ownership'] ?? null);

        self::assertTrue($payload['surfaceStatus']['ownership']);
        self::assertSame(7, $ownership['ownerUserId']);
        $finance = self::assertArrayPayload($payload['finance']);
        $metricOverview = self::assertArrayPayload($finance['metricOverview']);

        self::assertSame(1000, $metricOverview['gmv'] ?? null);
        self::assertSame(['crm', 'webhooks'], $payload['externalIntegration']['surfaces']);
    }

    public function testBuildSkipsOwnershipForNonNumericVendorId(): void
    {
        $this->ownershipProjectionBuilder->expects(self::never())->method('buildForVendorId');

        $this->financeRuntimeProjectionBuilder
            ->expects(self::once())
            ->method('build')
            ->with('vendor-abc', null, null, 'USD')
            ->willReturn(new VendorFinanceRuntimeProjection('vendor-abc', 'USD', null, [], null, null));

        $this->statementDeliveryRuntimeProjectionBuilder
            ->expects(self::once())
            ->method('build')
            ->with(self::callback(function (VendorStatementDeliveryRuntimeRequestDTO $request): bool {
                self::assertSame('vendor-abc', $request->vendorId);
                self::assertSame('', $request->from);
                self::assertSame('', $request->to);
                self::assertSame('USD', $request->currency);

                return true;
            }))
            ->willReturn(new VendorStatementDeliveryRuntimeProjection('vendor-abc', 'USD', null, [], null, []));

        $this->externalIntegrationRuntimeProjectionBuilder
            ->expects(self::once())
            ->method('build')
            ->with('vendor-abc')
            ->willReturn(new VendorExternalIntegrationRuntimeProjection('vendor-abc', null, [], [], [], []));

        $payload = $this->buildService()->build('vendor-abc')->toArray();

        self::assertNull($payload['ownership']);
        self::assertFalse($payload['surfaceStatus']['ownership']);
        self::assertTrue($payload['surfaceStatus']['finance']);
        self::assertFalse($payload['surfaceStatus']['statementDelivery']);
        self::assertTrue($payload['surfaceStatus']['externalIntegration']);
    }

    private function buildService(): VendorRuntimeStatusProjectionBuilder
    {
        return new VendorRuntimeStatusProjectionBuilder(
            $this->ownershipProjectionBuilder,
            $this->financeRuntimeProjectionBuilder,
            $this->statementDeliveryRuntimeProjectionBuilder,
            $this->externalIntegrationRuntimeProjectionBuilder,
        );
    }

    /** @return array<string, mixed> */
    private static function assertArrayPayload(mixed $value): array
    {
        if (!is_array($value)) {
            self::fail('Expected array payload.');
        }

        /* @var array<string, mixed> $value */
        return $value;
    }
}
