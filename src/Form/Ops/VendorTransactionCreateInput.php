<?php

declare(strict_types=1);

namespace App\Form\Ops;

final class VendorTransactionCreateInput
{
    public function __construct(
        public string $vendorId = '',
        public string $orderId = '',
        public ?string $projectId = null,
        public string $amount = '',
    ) {}
}
