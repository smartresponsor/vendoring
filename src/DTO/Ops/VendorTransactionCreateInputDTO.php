<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ops;

final class VendorTransactionCreateInputDTO
{
    public function __construct(
        public string $vendorId = '',
        public string $orderId = '',
        public ?string $projectId = null,
        public string $amount = '',
    ) {}
}
