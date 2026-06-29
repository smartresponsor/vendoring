<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Payout;

use App\Vendoring\DTO\Payout\VendorPayoutTransferDTO;
use App\Vendoring\Exception\Payout\VendorPayoutProviderNotConfiguredException;
use App\Vendoring\ServiceInterface\Payout\VendorPayoutProviderServiceInterface;

/**
 * Null-object payout provider for environments where no real provider is configured.
 *
 * This service is the safe default bound in services.yaml.
 * It never silently returns ok=true — it throws immediately so that
 * missing provider configuration surfaces at call time rather than producing
 * ghost transactions.
 *
 * To activate a real provider:
 *   1. Implement VendorPayoutProviderServiceInterface (e.g. StripePayoutProviderService).
 *   2. Re-bind VendorPayoutProviderServiceInterface in the host application services.yaml.
 */
final class VendorNullPayoutProviderService implements VendorPayoutProviderServiceInterface
{
    /**
     * @throws VendorPayoutProviderNotConfiguredException always
     */
    public function transfer(VendorPayoutTransferDTO $transfer): array
    {
        throw new VendorPayoutProviderNotConfiguredException($transfer->provider);
    }
}
