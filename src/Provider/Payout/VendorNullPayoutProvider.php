<?php

declare(strict_types=1);

namespace App\Vendoring\Provider\Payout;

use App\Vendoring\DTO\Payout\VendorPayoutTransferDTO;
use App\Vendoring\Exception\Payout\VendorPayoutProviderNotConfiguredException;
use App\Vendoring\ProviderInterface\Payout\VendorPayoutProviderInterface;

/**
 * Null-object payout provider for environments where no real provider is configured.
 *
 * This service is the safe default bound in services.yaml.
 * It never silently returns ok=true — it throws immediately so that
 * missing provider configuration surfaces at call time rather than producing
 * ghost transactions.
 *
 * To activate a real provider:
 *   1. Implement VendorPayoutProviderInterface (e.g. StripePayoutProviderService).
 *   2. Re-bind VendorPayoutProviderInterface in the host application services.yaml.
 */
final class VendorNullPayoutProvider implements VendorPayoutProviderInterface
{
    /**
     * @throws VendorPayoutProviderNotConfiguredException always
     */
    public function transfer(VendorPayoutTransferDTO $transfer): array
    {
        throw new VendorPayoutProviderNotConfiguredException($transfer->provider);
    }
}
