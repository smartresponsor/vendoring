<?php
declare(strict_types = 1);

namespace App\Service\Vendor\Payout;

use App\InfrastructureInterface\Vendor\Payout\PayoutProviderBridgeInterface;

final class PayoutProviderBridge implements PayoutProviderBridgeInterface
{
    public function transfer(string $tenantId, string $vendorId, string $provider, string $accountRef, float $amount, string $currency): array
    {
        // Stub provider call; integrate Stripe Connect / PayPal Payouts / bank wire later.
        $ref = $provider . '_payout_' . bin2hex(random_bytes(4));
        return ['ok' => true, 'ref' => $ref, 'error' => null];
    }
}
