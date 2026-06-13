<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Integration;

use App\Vendoring\Projection\Vendor\VendorExternalIntegrationRuntimeProjection;
use App\Vendoring\ServiceInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Payout\VendorPayoutProviderServiceInterface;
use App\Vendoring\ServiceInterface\Integration\VendorCrmServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\WebhooksConsumer\VendorWebhooksConsumerServiceInterface;

/**
 * Builds a vendor-local summary for neighboring integration seams.
 *
 * The builder is intentionally read-side only: it reports local readiness and
 * surface availability for CRM, webhook-consumer and payout bridge paths
 * without sending live external requests.
 */
final readonly class VendorExternalIntegrationRuntimeProjectionBuilderService implements VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface
{
    public function __construct(
        private VendorOwnershipProjectionBuilderServiceInterface    $ownershipProjectionBuilder,
        private VendorCrmServiceInterface              $crmService,
        private VendorWebhooksConsumerServiceInterface $webhooksConsumer,
        private VendorPayoutProviderServiceInterface   $payoutProviderBridge,
    ) {}

    public function build(string $tenantId, string $vendorId): VendorExternalIntegrationRuntimeProjection
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
