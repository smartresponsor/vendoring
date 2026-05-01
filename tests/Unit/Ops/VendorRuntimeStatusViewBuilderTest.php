<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Ops;

use App\Vendoring\Projection\Vendor\VendorExternalIntegrationRuntimeView;
use App\Vendoring\Projection\Vendor\VendorFinanceRuntimeView;
use App\Vendoring\Projection\Vendor\VendorOwnershipView;
use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\Projection\Vendor\VendorStatementDeliveryRuntimeView;
use App\Vendoring\Service\Ops\VendorRuntimeStatusViewBuilderService;
use App\Vendoring\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Finance\VendorFinanceRuntimeViewBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipViewBuilderServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeStatusViewBuilderTest extends TestCase
{
    private VendorOwnershipViewBuilderServiceInterface&MockObject $ownershipViewBuilder;
    private VendorFinanceRuntimeViewBuilderServiceInterface&MockObject $financeRuntimeViewBuilder;
    private VendorStatementDeliveryRuntimeViewBuilderServiceInterface&MockObject $statementDeliveryRuntimeViewBuilder;
    private VendorExternalIntegrationRuntimeViewBuilderServiceInterface&MockObject $externalIntegrationRuntimeViewBuilder;

    protected function setUp(): void
    {
        $this->ownershipViewBuilder = $this->createMock(VendorOwnershipViewBuilderServiceInterface::class);
        $this->financeRuntimeViewBuilder = $this->createMock(VendorFinanceRuntimeViewBuilderServiceInterface::class);
        $this->statementDeliveryRuntimeViewBuilder = $this->createMock(VendorStatementDeliveryRuntimeViewBuilderServiceInterface::class);
        $this->externalIntegrationRuntimeViewBuilder = $this->createMock(VendorExternalIntegrationRuntimeViewBuilderServiceInterface::class);
    }

    public function testBuildIncludesOwnershipSurfaceForNumericVendorId(): void
    {
        $this->ownershipViewBuilder
            ->expects(self::once())
            ->method('buildForVendorId')
            ->with(42)
            ->willReturn(new VendorOwnershipView(42, 7, []));

        $this->financeRuntimeViewBuilder
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', '42', '2025-01-01', '2025-01-31', 'USD')
            ->willReturn(new VendorFinanceRuntimeView('tenant-1', '42', 'USD', ['ownerUserId' => 7], ['gmv' => 1000], ['provider' => 'bank'], ['closing' => 900]));

        $this->statementDeliveryRuntimeViewBuilder
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
            ->willReturn(new VendorStatementDeliveryRuntimeView('tenant-1', '42', 'USD', ['ownerUserId' => 7], ['closing' => 900], ['path' => '/tmp/statement.csv'], [['email' => 'ops@example.com']]));

        $this->externalIntegrationRuntimeViewBuilder
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', '42')
            ->willReturn(new VendorExternalIntegrationRuntimeView('tenant-1', '42', ['ownerUserId' => 7], ['crm' => 'hubspot'], ['webhook' => 'ok'], ['payoutProvider' => 'bank'], ['crm', 'webhooks']));

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
        $this->ownershipViewBuilder->expects(self::never())->method('buildForVendorId');

        $this->financeRuntimeViewBuilder
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', 'vendor-abc', null, null, 'USD')
            ->willReturn(new VendorFinanceRuntimeView('tenant-1', 'vendor-abc', 'USD', null, [], null, null));

        $this->statementDeliveryRuntimeViewBuilder
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
            ->willReturn(new VendorStatementDeliveryRuntimeView('tenant-1', 'vendor-abc', 'USD', null, [], null, []));

        $this->externalIntegrationRuntimeViewBuilder
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', 'vendor-abc')
            ->willReturn(new VendorExternalIntegrationRuntimeView('tenant-1', 'vendor-abc', null, [], [], [], []));

        $payload = $this->buildService()->build('tenant-1', 'vendor-abc')->toArray();

        self::assertNull($payload['ownership']);
        self::assertFalse($payload['surfaceStatus']['ownership']);
        self::assertTrue($payload['surfaceStatus']['finance']);
        self::assertFalse($payload['surfaceStatus']['statementDelivery']);
        self::assertTrue($payload['surfaceStatus']['externalIntegration']);
    }

    private function buildService(): VendorRuntimeStatusViewBuilderService
    {
        return new VendorRuntimeStatusViewBuilderService(
            $this->ownershipViewBuilder,
            $this->financeRuntimeViewBuilder,
            $this->statementDeliveryRuntimeViewBuilder,
            $this->externalIntegrationRuntimeViewBuilder,
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
