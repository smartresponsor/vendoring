<?php

declare(strict_types=1);

namespace App\Service\Statement;

use App\ServiceInterface\Statement\VendorStatementRecipientProviderInterface;

final class VendorStatementRecipientProvider implements VendorStatementRecipientProviderInterface
{
    public function forPeriod(string $from, string $to): array
    {
        return [];
    }
}
