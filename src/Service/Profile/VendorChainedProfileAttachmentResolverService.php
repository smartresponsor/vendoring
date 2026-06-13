<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Profile;

use App\Vendoring\Projection\Vendor\VendorPublicProfileAttachmentProjection;
use App\Vendoring\ServiceInterface\Profile\VendorProfileAttachmentResolverServiceInterface;

/**
 * Resolves canonical Attaching media first and falls back to legacy Vendoring path fields.
 */
final readonly class VendorChainedProfileAttachmentResolverService implements VendorProfileAttachmentResolverServiceInterface
{
    public function __construct(
        private VendorProfileAttachmentResolverServiceInterface $primaryResolver,
        private VendorProfileAttachmentResolverServiceInterface $fallbackResolver,
    ) {
    }

    public function resolvePrimaryForVendorSlot(int $vendorId, string $slot): VendorPublicProfileAttachmentProjection
    {
        $primary = $this->primaryResolver->resolvePrimaryForVendorSlot($vendorId, $slot);

        if (null !== $primary->attachmentId || null !== $primary->url) {
            return $primary;
        }

        return $this->fallbackResolver->resolvePrimaryForVendorSlot($vendorId, $slot);
    }
}
