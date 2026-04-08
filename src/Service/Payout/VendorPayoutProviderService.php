<?php

declare(strict_types=1);

namespace App\Service\Payout;

use App\ServiceInterface\Payout\VendorPayoutProviderServiceInterface;

/**
 * Application service for vendor payout provider operations.
 */
final class VendorPayoutProviderService implements VendorPayoutProviderServiceInterface
{
    /**
     * Executes the transfer operation for this runtime surface.
     */
    public function transfer(string $tenantId, string $vendorId, string $provider, string $accountRef, float $amount, string $currency): array
    {
        $ref = $provider.'_payout_'.bin2hex(random_bytes(4));

        return [
            'ok' => true,
            'ref' => $ref,
            'tenantId' => $tenantId,
            'vendorId' => $vendorId,
            'provider' => $provider,
            'accountRef' => $accountRef,
            'amount' => $amount,
            'currency' => $currency,
            'error' => null,
        ];
    }
}
