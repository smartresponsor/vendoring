<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Profile;

use App\Vendoring\Projection\Vendor\VendorPublicProfileSummary;
use App\Vendoring\RepositoryInterface\Vendor\VendorProfileRepositoryInterface;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\ServiceInterface\Profile\VendorProfileAttachmentResolverServiceInterface;
use App\Vendoring\ServiceInterface\Profile\VendorPublicProfileSummaryProviderServiceInterface;
use App\Vendoring\ServiceInterface\Profile\VendorPublicProfileUrlGeneratorServiceInterface;
use App\Vendoring\ValueObject\VendorProfileAttachmentSlotValueObject;

/**
 * Builds the canonical public identity summary for external UI surfaces.
 */
final readonly class VendorPublicProfileSummaryProviderService implements VendorPublicProfileSummaryProviderServiceInterface
{
    public function __construct(
        private VendorRepositoryInterface $vendorRepository,
        private VendorProfileRepositoryInterface $profileRepository,
        private VendorProfileAttachmentResolverServiceInterface $attachmentResolver,
        private VendorPublicProfileUrlGeneratorServiceInterface $profileUrlGenerator,
    ) {
    }

    public function provideForVendorId(int $vendorId): ?VendorPublicProfileSummary
    {
        $vendor = $this->vendorRepository->find($vendorId);

        if (null === $vendor) {
            return null;
        }

        $profile = $this->profileRepository->findOneBy(['vendor' => $vendor]);
        $brandName = trim($vendor->getBrandName());
        $displayName = null === $profile?->getDisplayName() ? null : trim($profile->getDisplayName());
        $publicName = null !== $displayName && '' !== $displayName ? $displayName : $brandName;

        return new VendorPublicProfileSummary(
            vendorId: $vendorId,
            publicName: $publicName,
            brandName: $brandName,
            vendorStatus: $vendor->getStatus(),
            profileStatus: $profile?->getPublicProfileStatus() ?? 'draft',
            publishedAt: $profile?->getPublicProfilePublishedAt()?->format(DATE_ATOM),
            avatar: $this->attachmentResolver->resolvePrimaryForVendorSlot(
                $vendorId,
                VendorProfileAttachmentSlotValueObject::SLOT_AVATAR,
            ),
            cover: $this->attachmentResolver->resolvePrimaryForVendorSlot(
                $vendorId,
                VendorProfileAttachmentSlotValueObject::SLOT_COVER,
            ),
            profileUrl: $this->profileUrlGenerator->generateForVendorId($vendorId),
        );
    }

    public function provideForCurrentActor(?int $actorId): ?VendorPublicProfileSummary
    {
        if (null === $actorId || $actorId <= 0) {
            return null;
        }

        $vendor = $this->vendorRepository->findOneBy(['ownerUserId' => $actorId]);
        $vendorId = $vendor?->getId();

        if (!is_int($vendorId)) {
            return null;
        }

        return $this->provideForVendorId($vendorId);
    }
}
