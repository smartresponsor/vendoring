<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Support\Statement;

use App\Vendoring\DTO\Statement\VendorStatementRecipientDTO;
use App\Vendoring\ServiceInterface\Statement\VendorStatementRecipientProviderInterface;

final class FakeVendorStatementRecipientProvider implements VendorStatementRecipientProviderInterface
{
    /** @param list<VendorStatementRecipientDTO> $recipients */
    public function __construct(private readonly array $recipients) {}

    public function forPeriod(string $from, string $to): array
    {
        return $this->recipients;
    }
}
