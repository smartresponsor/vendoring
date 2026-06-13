<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Media;

use App\Vendoring\Projection\Vendor\VendorLegacyMediaAttachmentCandidate;
use App\Vendoring\RepositoryInterface\Vendor\VendorMediaRepositoryInterface;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\ServiceInterface\Media\VendorLegacyMediaAttachmentCandidateProviderServiceInterface;
use App\Vendoring\ValueObject\VendorProfileAttachmentSlotValueObject;

/**
 * Builds an explicit non-destructive migration plan from legacy Vendoring media paths.
 *
 * It does not create Attaching rows by itself. A host-level migration can consume these candidates
 * and create Attachment + AttachmentLink records through Attaching.
 */
final readonly class VendorLegacyMediaAttachmentCandidateProviderService implements VendorLegacyMediaAttachmentCandidateProviderServiceInterface
{
    public function __construct(
        private VendorRepositoryInterface $vendorRepository,
        private VendorMediaRepositoryInterface $mediaRepository,
    ) {
    }

    /** @return list<VendorLegacyMediaAttachmentCandidate> */
    public function provideForVendorId(int $vendorId): array
    {
        $vendor = $this->vendorRepository->find($vendorId);

        if (null === $vendor) {
            return [];
        }

        $media = $this->mediaRepository->findOneBy(['vendor' => $vendor]);

        if (null === $media) {
            return [];
        }

        $candidates = [];
        $ownerId = (string) $vendorId;

        $this->appendCandidate(
            $candidates,
            $vendorId,
            VendorProfileAttachmentSlotValueObject::CONTEXT_PROFILE,
            VendorProfileAttachmentSlotValueObject::SLOT_AVATAR,
            $media->getLogoPath(),
            true,
        );
        $this->appendCandidate(
            $candidates,
            $vendorId,
            VendorProfileAttachmentSlotValueObject::CONTEXT_PROFILE,
            VendorProfileAttachmentSlotValueObject::SLOT_COVER,
            $media->getBannerPath(),
            true,
        );

        foreach ($media->getGallery() ?? [] as $position => $legacyPath) {
            $this->appendCandidate(
                $candidates,
                $vendorId,
                VendorProfileAttachmentSlotValueObject::CONTEXT_MEDIA,
                VendorProfileAttachmentSlotValueObject::SLOT_GALLERY,
                is_string($legacyPath) ? $legacyPath : null,
                false,
                is_int($position) ? $position : null,
            );
        }

        return $candidates;
    }

    /** @param list<VendorLegacyMediaAttachmentCandidate> $candidates */
    private function appendCandidate(
        array &$candidates,
        int $vendorId,
        string $context,
        string $slot,
        ?string $legacyPath,
        bool $primary,
        ?int $position = null,
    ): void {
        if (null === $legacyPath || '' === trim($legacyPath)) {
            return;
        }

        $candidates[] = new VendorLegacyMediaAttachmentCandidate(
            vendorId: $vendorId,
            ownerType: VendorProfileAttachmentSlotValueObject::OWNER_TYPE_VENDOR,
            ownerId: (string) $vendorId,
            context: $context,
            slot: $slot,
            legacyPath: trim($legacyPath),
            primary: $primary,
            position: $position,
        );
    }
}
