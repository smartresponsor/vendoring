<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Profile;

use App\Vendoring\Entity\Vendor\VendorProfileAvatarEntity;
use App\Vendoring\Entity\Vendor\VendorProfileCoverEntity;
use App\Vendoring\Projection\Vendor\VendorPublicProfileAttachmentProjection;
use App\Vendoring\RepositoryInterface\Vendor\VendorMediaRepositoryInterface;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\ServiceInterface\Profile\VendorProfileAttachmentResolverServiceInterface;
use App\Vendoring\ValueObject\VendorProfileAttachmentSlotValueObject;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Reads legacy Vendoring media fields as a temporary public-profile fallback.
 *
 * This service is intentionally not the canonical media owner. Avatar and cover files should be
 * resolved through Attaching primary links. The legacy resolver keeps existing UI surfaces working
 * while old logo/banner/profile-avatar/profile-cover rows are migrated non-destructively.
 */
final readonly class VendorLegacyProfileAttachmentResolverService implements VendorProfileAttachmentResolverServiceInterface
{
    public function __construct(
        private VendorRepositoryInterface $vendorRepository,
        private VendorMediaRepositoryInterface $mediaRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function resolvePrimaryForVendorSlot(int $vendorId, string $slot): VendorPublicProfileAttachmentProjection
    {
        $vendor = $this->vendorRepository->find($vendorId);

        if (null === $vendor) {
            return VendorPublicProfileAttachmentProjection::empty();
        }

        $path = match ($slot) {
            VendorProfileAttachmentSlotValueObject::SLOT_AVATAR => $this->resolveLegacyAvatarPath($vendor),
            VendorProfileAttachmentSlotValueObject::SLOT_COVER => $this->resolveLegacyCoverPath($vendor),
            default => null,
        };

        if (null === $path || '' === trim($path)) {
            return VendorPublicProfileAttachmentProjection::empty();
        }

        return new VendorPublicProfileAttachmentProjection(
            attachmentId: null,
            url: $this->normalizeLegacyPublicUrl($path),
        );
    }

    private function resolveLegacyAvatarPath(object $vendor): ?string
    {
        $avatar = $this->entityManager->getRepository(VendorProfileAvatarEntity::class)->findOneBy(['vendor' => $vendor]);

        if ($avatar instanceof VendorProfileAvatarEntity && '' !== trim($avatar->getFilePath())) {
            return $avatar->getFilePath();
        }

        $media = $this->mediaRepository->findOneBy(['vendor' => $vendor]);

        return $media?->getLogoPath();
    }

    private function resolveLegacyCoverPath(object $vendor): ?string
    {
        $cover = $this->entityManager->getRepository(VendorProfileCoverEntity::class)->findOneBy(['vendor' => $vendor]);

        if ($cover instanceof VendorProfileCoverEntity && '' !== trim($cover->getFilePath())) {
            return $cover->getFilePath();
        }

        $media = $this->mediaRepository->findOneBy(['vendor' => $vendor]);

        return $media?->getBannerPath();
    }

    private function normalizeLegacyPublicUrl(string $path): string
    {
        $normalized = trim($path);

        if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://') || str_starts_with($normalized, '/')) {
            return $normalized;
        }

        return '/'.ltrim($normalized, '/');
    }
}
