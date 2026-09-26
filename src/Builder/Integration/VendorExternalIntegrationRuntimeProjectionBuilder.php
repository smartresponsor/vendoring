<?php

declare(strict_types=1);

namespace App\Vendoring\Builder\Integration;

use App\Vendoring\BuilderInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Ownership\VendorOwnershipProjectionBuilderInterface;
use App\Vendoring\Projection\VendorExternalIntegrationRuntimeProjection;
use App\Vendoring\ProviderInterface\Payout\VendorPayoutProviderInterface;
use App\Vendoring\ServiceInterface\Integration\VendorCrmServiceInterface;
use App\Vendoring\ServiceInterface\WebhooksConsumer\VendorWebhooksConsumerServiceInterface;

/**
 * Builds a vendor-local summary for neighboring integration seams.
 *
 * The builder is intentionally read-side only: it reports local readiness and
 * surface availability for CRM, webhook-consumer and payout bridge paths
 * without sending live external requests.
 */
final readonly class VendorExternalIntegrationRuntimeProjectionBuilder implements VendorExternalIntegrationRuntimeProjectionBuilderInterface
{
    public function __construct(
        private VendorOwnershipProjectionBuilderInterface $ownershipProjectionBuilder,
        private VendorCrmServiceInterface $crmService,
        private VendorWebhooksConsumerServiceInterface $webhooksConsumer,
        private VendorPayoutProviderInterface $payoutProviderBridge,
    ) {
    }

    public function build(string $vendorId): VendorExternalIntegrationRuntimeProjection
    {
        $ownership = null;
        if (ctype_digit($vendorId)) {
            $ownershipProjection = $this->ownershipProjectionBuilder->buildForVendorId((int) $vendorId);
            $ownership = $ownershipProjection?->toArray();
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

        return new VendorExternalIntegrationRuntimeProjection(
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
