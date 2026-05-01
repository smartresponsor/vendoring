<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Statement;

use App\Vendoring\DTO\Statement\VendorStatementRecipientDTO;

interface VendorStatementRecipientProviderServiceInterface
{
    /** @return list<VendorStatementRecipientDTO> */
    public function forPeriod(string $from, string $to): array;
}
