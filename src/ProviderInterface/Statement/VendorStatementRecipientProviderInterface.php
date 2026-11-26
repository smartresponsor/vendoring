<?php

declare(strict_types=1);

namespace App\Vendoring\ProviderInterface\Statement;

use App\Vendoring\DTO\Statement\VendorStatementRecipientDTO;

interface VendorStatementRecipientProviderInterface
{
    /** @return list<VendorStatementRecipientDTO> */
    public function forPeriod(string $from, string $to): array;
}
