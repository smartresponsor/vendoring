<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface;

use App\Vendoring\Entity\Vendor\VendorEntity;

interface VendorOwnershipProjectionRepositoryInterface
{
    /** @return array<string, int> */
    public function relationCounts(VendorEntity $vendor): array;
}
