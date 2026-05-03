<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Ops;

use App\Vendoring\Projection\Vendor\VendorExternalIntegrationRuntimeProjection;
use App\Vendoring\Projection\Vendor\VendorFinanceRuntimeProjection;
use App\Vendoring\Projection\Vendor\VendorOwnershipProjection;
use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\Projection\Vendor\VendorStatementDeliveryRuntimeProjection;
use App\Vendoring\Service\Ops\VendorRuntimeStatusProjectionBuilderService;
use App\Vendoring\ServiceInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Finance\VendorFinanceRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipProjectionBuilderServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeStatusProjectionBuilderTest extends TestCase
{
    private VendorOwnershipProjectionBuilderServiceInterface&MockObject $ownershipProjectionBuilder;
    private VendorFinanceRuntimeProjectionBuilderServiceInterface&MockObject $financeRuntimeProjectionBuilder;
    private VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface&MockObject $statementDeliveryRuntimeProjectionBuilder;
    private VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface&MockObject $externalIntegrationRuntimeProjectionBuilder;

    protected function setUp(): void
    {
        $this->ownershipProjectionBuilder = $this->createMock(VendorOwnershipProjectionBuilderServiceInterface::class);
        $this->financeRuntimeProjectionBuilder = $this->createMock(VendorFinanceRuntimeProjectionBuilderServiceInterface::class);
        $this->statementDeliveryRuntimeProjectionBuilder = $this->createMock(VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface::class);
        $this->externalIntegrationRuntimeProjectionBuilder = $this->createMock(VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface::class);
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
            ->with('tenant-1', '42', '2025-01-01', '2025-01-31', 'USD')
            ->willReturn(new VendorFinanceRuntimeProjection('tenant-1', '42', 'USD', ['ownerUserId' => 7], ['gmv' => 1000], ['provider' => 'bank'], ['closing' => 900]));

        $this->statementDeliveryRuntimeProjectionBuilder
            ->expects(self::once())
            ->method('build')
            ->with(self::callback(function (VendorStatementDeliveryRuntimeRequestDTO $request): bool {
                self::assertSame('tenant-1', $request->tenantId);
                self::assertSame('42', $request->vendorId);
                self::assertSame('2025-01-01', $request->from);
                self::assertSame('2025-01-31', $request->to);
                self::assertSame('USD', $request->currency);

                return true;
            }))
            ->willReturn(new VendorStatementDeliveryRuntimeProjection('tenant-1', '42', 'USD', ['ownerUserId' => 7], ['closing' => 900], ['path' => '/tmp/statement.csv'], [['email' => 'ops@example.com']]));

        $this->externalIntegrationRuntimeProjectionBuilder
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', '42')
            ->willReturn(new VendorExternalIntegrationRuntimeProjection('tenant-1', '42', ['ownerUserId' => 7], ['crm' => 'hubspot'], ['webhook' => 'ok'], ['payoutProvider' => 'bank'], ['crm', 'webhooks']));

        $payload = $this->buildService()->build('tenant-1', '42', '2025-01-01', '2025-01-31', 'USD')->toArray();
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
            ->with('tenant-1', 'vendor-abc', null, null, 'USD')
            ->willReturn(new VendorFinanceRuntimeProjection('tenant-1', 'vendor-abc', 'USD', null, [], null, null));

        $this->statementDeliveryRuntimeProjectionBuilder
            ->expects(self::once())
            ->method('build')
            ->with(self::callback(function (VendorStatementDeliveryRuntimeRequestDTO $request): bool {
                self::assertSame('tenant-1', $request->tenantId);
                self::assertSame('vendor-abc', $request->vendorId);
                self::assertSame('', $request->from);
                self::assertSame('', $request->to);
                self::assertSame('USD', $request->currency);

                return true;
            }))
            ->willReturn(new VendorStatementDeliveryRuntimeProjection('tenant-1', 'vendor-abc', 'USD', null, [], null, []));

        $this->externalIntegrationRuntimeProjectionBuilder
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', 'vendor-abc')
            ->willReturn(new VendorExternalIntegrationRuntimeProjection('tenant-1', 'vendor-abc', null, [], [], [], []));

        $payload = $this->buildService()->build('tenant-1', 'vendor-abc')->toArray();

        self::assertNull($payload['ownership']);
        self::assertFalse($payload['surfaceStatus']['ownership']);
        self::assertTrue($payload['surfaceStatus']['finance']);
        self::assertFalse($payload['surfaceStatus']['statementDelivery']);
        self::assertTrue($payload['surfaceStatus']['externalIntegration']);
    }

    private function buildService(): VendorRuntimeStatusProjectionBuilderService
    {
        return new VendorRuntimeStatusProjectionBuilderService(
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

        /** @var array<string, mixed> $value */
        return $value;
    }
}
