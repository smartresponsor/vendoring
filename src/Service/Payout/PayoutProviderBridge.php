<?php

declare(strict_types=1);

namespace App\Service\Payout;

use App\ServiceInterface\Payout\PayoutProviderBridgeInterface;

final class PayoutProviderBridge implements PayoutProviderBridgeInterface
{
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
