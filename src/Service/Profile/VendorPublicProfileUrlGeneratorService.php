<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Profile;

use App\Vendoring\ServiceInterface\Profile\VendorPublicProfileUrlGeneratorServiceInterface;

/**
 * Template-based public profile URL generator for standalone component installs.
 */
final readonly class VendorPublicProfileUrlGeneratorService implements VendorPublicProfileUrlGeneratorServiceInterface
{
    public function __construct(
        private string $profilePathTemplate = '/vendor/{vendorId}',
    ) {
    }

    public function generateForVendorId(int $vendorId): string
    {
        return str_replace('{vendorId}', (string) $vendorId, $this->profilePathTemplate);
    }
}
