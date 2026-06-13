<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Interfacing;

use App\Vendoring\ServiceInterface\Interfacing\VendorInterfacingTemplateCandidateProviderServiceInterface;
use App\Vendoring\ValueObject\Interfacing\VendorInterfacingSurfaceNameValueObject;

/**
 * Builds canonical noun-surface template candidates for Vendoring.
 *
 * Interfacing owns the templates under its `templates/vendor/...` noun surface. Vendoring only
 * knows the lookup convention and tries the Interfacing Twig namespace first, then the plain
 * `vendor/...` path for standalone/local execution where the same template root is registered
 * without a namespace.
 */
final class VendorInterfacingTemplateCandidateProviderService implements VendorInterfacingTemplateCandidateProviderServiceInterface
{
    /**
     * @return list<string>
     */
    public function candidatesFor(string $surfaceName): array
    {
        return match ($surfaceName) {
            VendorInterfacingSurfaceNameValueObject::INDEX => [
                '@Interfacing/vendor/index',
                'vendor/index',
            ],
            VendorInterfacingSurfaceNameValueObject::PROFILE => [
                '@Interfacing/vendor/profile/show',
                '@Interfacing/vendor/profile/index',
                '@Interfacing/vendor/profile',
                '@Interfacing/vendor/index',
                'vendor/profile/show',
                'vendor/profile/index',
                'vendor/profile',
                'vendor/index',
            ],
            default => [],
        };
    }
}
