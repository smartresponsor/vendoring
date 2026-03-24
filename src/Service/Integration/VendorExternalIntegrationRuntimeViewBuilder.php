<?php

declare(strict_types=1);

namespace App\Service\Integration;

use App\Projection\VendorExternalIntegrationRuntimeView;
use App\ServiceInterface\CrmServiceInterface;
use App\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderInterface;
use App\ServiceInterface\Payout\PayoutProviderBridgeInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;
use App\ServiceInterface\WebhooksConsumer\WebhooksConsumerServiceInterface;

/**
 * Builds a vendor-local summary for neighboring integration seams.
 *
 * The builder is intentionally read-side only: it reports local readiness and
 * surface availability for CRM, webhook-consumer and payout bridge paths
 * without sending live external requests.
 */
final class VendorExternalIntegrationRuntimeViewBuilder implements VendorExternalIntegrationRuntimeViewBuilderInterface
{
    public function __construct(
        private readonly VendorOwnershipViewBuilderInterface $ownershipViewBuilder,
        private readonly CrmServiceInterface $crmService,
        private readonly WebhooksConsumerServiceInterface $webhooksConsumer,
        private readonly PayoutProviderBridgeInterface $payoutProviderBridge,
    ) {
    }

    public function build(string $tenantId, string $vendorId): VendorExternalIntegrationRuntimeView
    {
        $ownership = null;
        if (ctype_digit($vendorId)) {
            $ownershipView = $this->ownershipViewBuilder->buildForVendorId((int) $vendorId);
            $ownership = $ownershipView?->toArray();
        }

        $crm = [
            'serviceClass' => $this->crmService::class,
            'registerMode' => 'write-only',
            'runtimeReadable' => false,
            'providerConfigured' => false,
        ];

        $webhooks = [
            'consumerClass' => $this->webhooksConsumer::class,
            'consumerReady' => $this->webhooksConsumer->ok(),
            'mode' => 'consumer-only',
        ];

        $payoutBridge = [
            'bridgeClass' => $this->payoutProviderBridge::class,
            'transferMode' => 'write-only',
            'runtimeReadable' => false,
        ];

        return new VendorExternalIntegrationRuntimeView(
            tenantId: $tenantId,
            vendorId: $vendorId,
            ownership: $ownership,
            crm: $crm,
            webhooks: $webhooks,
            payoutBridge: $payoutBridge,
            surfaces: [
                'crm.registerVendor',
                'webhooks.consumer',
                'payout.transfer',
            ],
        );
    }
}
