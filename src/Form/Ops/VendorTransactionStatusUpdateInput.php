<?php

declare(strict_types=1);

namespace App\Form\Ops;

final class VendorTransactionStatusUpdateInput
{
    public function __construct(
        public string $status = 'pending',
    ) {
    }
}
