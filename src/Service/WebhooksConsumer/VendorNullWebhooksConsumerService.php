<?php

declare(strict_types=1);

namespace App\Vendoring\Service\WebhooksConsumer;

use App\Vendoring\ServiceInterface\WebhooksConsumer\VendorWebhooksConsumerServiceInterface;

/**
 * Null-object webhook consumer for environments where no real webhook provider is configured.
 *
 * The previous implementation returned true unconditionally, causing the canary rollout
 * coordinator to treat the webhook surface as healthy regardless of actual provider state.
 *
 * Returns false explicitly — an unconfigured webhook provider is not healthy.
 * When a real consumer is configured, it must implement VendorWebhooksConsumerServiceInterface
 * and override this binding in the host application services.yaml.
 */
final class VendorNullWebhooksConsumerService implements VendorWebhooksConsumerServiceInterface
{
    public function ok(): bool
    {
        return false;
    }
}
