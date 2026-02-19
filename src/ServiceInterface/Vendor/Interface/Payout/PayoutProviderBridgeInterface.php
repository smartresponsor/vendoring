<?php
declare(strict_types = 1);

namespace App\ServiceInterface\Vendor\Interface\Payout;
interface PayoutProviderBridgeInterface
{
    /** @return array{ok:bool, ref:?string, error:?string} */
    public function transfer(string $tenantId, string $vendorId, string $provider, string $accountRef, float $amount, string $currency): array;
}
