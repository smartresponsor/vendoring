<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Profile;

use App\Vendoring\DTO\VendorProfileDTO;

interface VendorProfileRequestResolverServiceInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function resolve(int $vendorId, array $payload): VendorProfileDTO;
}
