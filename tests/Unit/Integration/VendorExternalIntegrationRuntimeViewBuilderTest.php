<?php

declare(strict_types=1);

namespace App\Tests\Unit\Integration;

use App\Projection\VendorOwnershipView;
use App\Service\Integration\VendorExternalIntegrationRuntimeViewBuilder;
use App\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderInterface;
use App\ServiceInterface\Payout\VendorPayoutProviderServiceInterface;
use App\ServiceInterface\VendorCrmServiceInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;
use App\ServiceInterface\WebhooksConsumer\VendorWebhooksConsumerServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorExternalIntegrationRuntimeViewBuilderTest extends TestCase
{
    private VendorOwnershipViewBuilderInterface&MockObject $ownership;
    private VendorCrmServiceInterface&MockObject $crm;
    private VendorWebhooksConsumerServiceInterface&MockObject $webhooks;
    private VendorPayoutProviderServiceInterface&MockObject $payoutBridge;

    protected function setUp(): void
    {
        $this->ownership = $this->createMock(VendorOwnershipViewBuilderInterface::class);
        $this->crm = $this->createMock(VendorCrmServiceInterface::class);
        $this->webhooks = $this->createMock(VendorWebhooksConsumerServiceInterface::class);
        $this->payoutBridge = $this->createMock(VendorPayoutProviderServiceInterface::class);
    }

    public function testBuildIncludesOwnershipForNumericVendorIdAndWebhookReadiness(): void
    {
        $this->ownership
            ->expects(self::once())
            ->method('buildForVendorId')
            ->with(101)
            ->willReturn(new VendorOwnershipView(101, 5001, [['userId' => 5002, 'role' => 'manager']]));
        $this->webhooks->expects(self::once())->method('ok')->willReturn(true);

        $payload = (new VendorExternalIntegrationRuntimeViewBuilder(
            $this->ownership,
            $this->crm,
            $this->webhooks,
            $this->payoutBridge,
        ))->build('tenant-1', '101')->toArray();

        self::assertSame('tenant-1', $payload['tenantId']);
        self::assertSame('101', $payload['vendorId']);
        self::assertSame(5001, $payload['ownership']['ownerUserId']);
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

        $payload = (new VendorExternalIntegrationRuntimeViewBuilder(
            $this->ownership,
            $this->crm,
            $this->webhooks,
            $this->payoutBridge,
        ))->build('tenant-1', 'vendor-alpha')->toArray();

        self::assertNull($payload['ownership']);
        self::assertSame('vendor-alpha', $payload['vendorId']);
        self::assertFalse($payload['webhooks']['consumerReady']);
    }
}
