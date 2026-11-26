<?php

declare(strict_types=1);

namespace App\Vendoring\Provider\Payout;

use App\Vendoring\DTO\Payout\VendorPayoutTransferDTO;
use App\Vendoring\Exception\Payout\VendorPayoutProviderNotConfiguredException;
use App\Vendoring\ProviderInterface\Payout\VendorPayoutProviderInterface;

final class VendorPayoutProvider implements VendorPayoutProviderInterface
{
    /** @throws VendorPayoutProviderNotConfiguredException */
    public function transfer(VendorPayoutTransferDTO $transfer): array
    {
        // Retain the published class without ever fabricating a successful transfer.
        throw new VendorPayoutProviderNotConfiguredException($transfer->provider);
    }
}
