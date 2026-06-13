<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Profile;

/**
 * Generates the vendor-owned public profile URL used by UI shells.
 *
 * Host applications can replace this service when their route tree differs from the default
 * component-local convention.
 */
interface VendorPublicProfileUrlGeneratorServiceInterface
{
    public function generateForVendorId(int $vendorId): string;
}
