<?php

declare(strict_types=1);

namespace App\ServiceInterface\Policy;

interface VendorTransactionAmountPolicyInterface
{
    public function normalize(string $amount): string;
}
