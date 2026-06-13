<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ops;

final class VendorTransactionStatusUpdateInputDTO
{
    public function __construct(
        public string $status = 'pending',
    ) {}
}
