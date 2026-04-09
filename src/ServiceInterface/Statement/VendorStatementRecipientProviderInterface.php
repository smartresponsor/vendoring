<?php

declare(strict_types=1);

namespace App\ServiceInterface\Statement;

use App\DTO\Statement\VendorStatementRecipientDTO;

interface VendorStatementRecipientProviderInterface
{
    /** @return list<VendorStatementRecipientDTO> */
    public function forPeriod(string $from, string $to): array;
}
