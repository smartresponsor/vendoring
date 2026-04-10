<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\DTO\VendorProfileDTO;

interface VendorProfileRequestResolverInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function resolve(int $vendorId, array $payload): VendorProfileDTO;
}
