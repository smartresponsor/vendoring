<?php

declare(strict_types=1);

namespace App\Service\Statement;

use App\ServiceInterface\Statement\VendorStatementRecipientProviderInterface;

/**
 * Provides vendor statement recipient.
 */
final class VendorStatementRecipientProvider implements VendorStatementRecipientProviderInterface
{
    /**
     * Executes the for period operation for this runtime surface.
     */
    public function forPeriod(string $from, string $to): array
    {
        return [];
    }
}
