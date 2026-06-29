<?php

declare(strict_types=1);

namespace App\Vendoring\Exception\Payout;

/**
 * Thrown when a payout transfer is attempted but no real provider is configured.
 *
 * This exception is intentionally explicit: a silent no-op in production
 * is worse than a hard failure. Callers must configure a real payout provider
 * before any transfer can proceed.
 */
final class VendorPayoutProviderNotConfiguredException extends \RuntimeException
{
    public function __construct(string $provider)
    {
        parent::__construct(
            sprintf(
                'Payout provider "%s" is not configured. '
                .'Bind a concrete implementation of VendorPayoutProviderServiceInterface '
                .'or configure the provider credentials before initiating transfers.',
                $provider,
            ),
        );
    }
}
