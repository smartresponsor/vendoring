<?php

declare(strict_types=1);

namespace App\ServiceInterface\Policy;

interface VendorTransactionStatusPolicyInterface
{
    public function normalize(string $status): string;

    public function canTransition(string $fromStatus, string $toStatus): bool;
}
