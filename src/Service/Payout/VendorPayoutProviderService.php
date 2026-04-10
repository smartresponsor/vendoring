<?php

declare(strict_types=1);

namespace App\Service\Payout;

use App\DTO\Payout\VendorPayoutTransferDTO;
use App\ServiceInterface\Payout\VendorPayoutProviderServiceInterface;

final class VendorPayoutProviderService implements VendorPayoutProviderServiceInterface
{
    public function transfer(VendorPayoutTransferDTO $transfer): array
    {
        $ref = $transfer->provider.'_payout_'.bin2hex(random_bytes(4));

        return [
            'ok' => true,
            'ref' => $ref,
            'tenantId' => $transfer->tenantId,
            'vendorId' => $transfer->vendorId,
            'provider' => $transfer->provider,
            'accountRef' => $transfer->accountRef,
            'amount' => $transfer->amount,
            'currency' => $transfer->currency,
            'error' => null,
        ];
    }
}
