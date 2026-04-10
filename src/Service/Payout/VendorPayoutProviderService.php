<?php

declare(strict_types=1);

namespace App\Service\Payout;

use App\DTO\Payout\VendorPayoutTransferDTO;
use App\ServiceInterface\Payout\VendorPayoutProviderServiceInterface;
use Random\RandomException;

final class VendorPayoutProviderService implements VendorPayoutProviderServiceInterface
{
    /**
     * @return array<string, mixed>
     * @throws RandomException
     */
    public function transfer(VendorPayoutTransferDTO $transfer): array
    {
        $reference = $transfer->provider.'_payout_'.bin2hex(random_bytes(4));

        return [
            'ok' => true,
            'ref' => $reference,
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
