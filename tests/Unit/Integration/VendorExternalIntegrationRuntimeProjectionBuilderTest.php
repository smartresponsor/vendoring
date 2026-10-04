<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Integration;

use App\Vendoring\Builder\Integration\VendorExternalIntegrationRuntimeProjectionBuilder;
use App\Vendoring\BuilderInterface\Ownership\VendorOwnershipProjectionBuilderInterface;
use App\Vendoring\Projection\VendorOwnershipProjection;
use App\Vendoring\ProviderInterface\Payout\VendorPayoutProviderInterface;
use App\Vendoring\ServiceInterface\Integration\VendorCrmServiceInterface;
use App\Vendoring\ServiceInterface\WebhooksConsumer\VendorWebhooksConsumerServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorExternalIntegrationRuntimeProjectionBuilderTest extends TestCase
{
    private VendorOwnershipProjectionBuilderInterface&MockObject $ownership;
    private VendorCrmServiceInterface&MockObject $crm;
    private VendorWebhooksConsumerServiceInterface&MockObject $webhooks;
    private VendorPayoutProviderInterface&MockObject $payoutBridge;

    protected function setUp(): void
    {
        $this->ownership = $this->createMock(VendorOwnershipProjectionBuilderInterface::class);
        $this->crm = $this->createMock(VendorCrmServiceInterface::class);
        $this->webhooks = $this->createMock(VendorWebhooksConsumerServiceInterface::class);
        $this->payoutBridge = $this->createMock(VendorPayoutProviderInterface::class);
    }

    public function testBuildIncludesOwnershipForNumericVendorIdAndWebhookReadiness(): void
    {
        $this->ownership
            ->expects(self::once())
            ->method('buildForVendorId')
            ->with(101)
            ->willReturn(new VendorOwnershipProjection(101, 5001, [[
                'userId' => 5002,
                'role' => 'manager',
                'status' => 'active',
                'isPrimary' => false,
                'grantedAt' => '2026-03-31T10:00:00+00:00',
                'revokedAt' => null,
                'capabilities' => [],
            ]]));
        $this->webhooks->expects(self::once())->method('ok')->willReturn(true);

        $payload = (new VendorExternalIntegrationRuntimeProjectionBuilder(
            $this->ownership,
            $this->crm,
            $this->webhooks,
            $this->payoutBridge,
        ))->build('101')->toArray();

        self::assertSame('101', $payload['vendorId']);
        self::assertIsArray($payload['ownership'] ?? null);
        self::assertSame(5001, $payload['ownership']['ownerUserId'] ?? null);
        self::assertSame($this->crm::class, $payload['crm']['serviceClass']);
        self::assertSame('write-only', $payload['crm']['registerMode']);
        self::assertFalse($payload['crm']['runtimeReadable']);
        self::assertFalse($payload['crm']['providerConfigured']);
        self::assertSame($this->webhooks::class, $payload['webhooks']['consumerClass']);
        self::assertTrue($payload['webhooks']['consumerReady']);
        self::assertSame('consumer-only', $payload['webhooks']['mode']);
        self::assertSame($this->payoutBridge::class, $payload['payoutBridge']['bridgeClass']);
        self::assertSame('write-only', $payload['payoutBridge']['transferMode']);
        self::assertFalse($payload['payoutBridge']['runtimeReadable']);
        self::assertSame(['crm.registerVendor', 'webhooks.consumer', 'payout.transfer'], $payload['surfaces']);
    }

    public function testBuildSkipsOwnershipForNonNumericVendorId(): void
    {
        $this->ownership->expects(self::never())->method('buildForVendorId');
        $this->webhooks->expects(self::once())->method('ok')->willReturn(false);

        $payload = (new VendorExternalIntegrationRuntimeProjectionBuilder(
            $this->ownership,
            $this->crm,
            $this->webhooks,
            $this->payoutBridge,
        ))->build('vendor-alpha')->toArray();

        self::assertNull($payload['ownership']);
        self::assertSame('vendor-alpha', $payload['vendorId']);
        self::assertFalse($payload['webhooks']['consumerReady']);
    }
}
