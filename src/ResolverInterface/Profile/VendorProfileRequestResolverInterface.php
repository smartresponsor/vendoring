<?php

declare(strict_types=1);

namespace App\Vendoring\ResolverInterface\Profile;

use App\Vendoring\DTO\VendorProfileDTO;

interface VendorProfileRequestResolverInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function resolve(int $vendorId, array $payload): VendorProfileDTO;
}
