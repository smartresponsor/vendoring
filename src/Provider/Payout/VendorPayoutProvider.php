<?php

declare(strict_types=1);

namespace App\Vendoring\Provider\Payout;

use App\Vendoring\DTO\Payout\VendorPayoutTransferDTO;
use App\Vendoring\ProviderInterface\Payout\VendorPayoutProviderInterface;
use Random\RandomException;

final class VendorPayoutProvider implements VendorPayoutProviderInterface
{
    /**
     * @return array<string, mixed>
     *
     * @throws RandomException
     */
    public function transfer(VendorPayoutTransferDTO $transfer): array
    {
        $reference = $transfer->provider.'_payout_'.bin2hex(random_bytes(4));

        return [
            'ok' => true,
            'ref' => $reference,
            'vendorId' => $transfer->vendorId,
            'provider' => $transfer->provider,
            'accountRef' => $transfer->accountRef,
            'amount' => $transfer->amount,
            'currency' => $transfer->currency,
            'error' => null,
        ];
    }
}
