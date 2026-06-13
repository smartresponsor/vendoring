<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Profile\Bridge;

use App\Vendoring\Projection\Vendor\VendorPublicProfileAttachmentProjection;
use App\Vendoring\ServiceInterface\Profile\VendorProfileAttachmentResolverServiceInterface;
use App\Vendoring\ValueObject\VendorProfileAttachmentSlotValueObject;

/**
 * Resolves Vendoring public-profile media through the Attaching primary-link contract.
 *
 * This bridge intentionally avoids making Vendoring own stored files or hard-coding an Attaching
 * package requirement. The host application wires the concrete Attaching resolver when both
 * components are installed together; Vendoring keeps a null resolver for standalone operation.
 */
final readonly class VendorAttachingProfileAttachmentResolverService implements VendorProfileAttachmentResolverServiceInterface
{
    public function __construct(
        private object $attachmentPrimaryLinkResolver,
    ) {
    }

    public function resolvePrimaryForVendorSlot(int $vendorId, string $slot): VendorPublicProfileAttachmentProjection
    {
        if (!in_array($slot, VendorProfileAttachmentSlotValueObject::publicProfileSlots(), true)) {
            return VendorPublicProfileAttachmentProjection::empty();
        }

        if (!method_exists($this->attachmentPrimaryLinkResolver, 'resolvePrimary')) {
            return VendorPublicProfileAttachmentProjection::empty();
        }

        $primaryLinkView = $this->attachmentPrimaryLinkResolver->resolvePrimary(
            VendorProfileAttachmentSlotValueObject::OWNER_TYPE_VENDOR,
            (string) $vendorId,
            VendorProfileAttachmentSlotValueObject::CONTEXT_PROFILE,
            $slot,
        );

        if (!is_object($primaryLinkView) || !property_exists($primaryLinkView, 'attachment')) {
            return VendorPublicProfileAttachmentProjection::empty();
        }

        $attachment = $primaryLinkView->attachment;

        if (!is_object($attachment)) {
            return VendorPublicProfileAttachmentProjection::empty();
        }

        return new VendorPublicProfileAttachmentProjection(
            attachmentId: $this->readIntProperty($attachment, 'id'),
            url: $this->readNullableStringProperty($attachment, 'downloadUrl'),
            altText: $this->readNullableStringProperty($attachment, 'altText'),
            mimeType: $this->readNullableStringProperty($attachment, 'mimeType'),
            width: $this->readNullableIntProperty($attachment, 'width'),
            height: $this->readNullableIntProperty($attachment, 'height'),
        );
    }

    private function readIntProperty(object $object, string $property): ?int
    {
        if (!property_exists($object, $property)) {
            return null;
        }

        $value = $object->{$property};

        return is_int($value) ? $value : null;
    }

    private function readNullableIntProperty(object $object, string $property): ?int
    {
        if (!property_exists($object, $property)) {
            return null;
        }

        $value = $object->{$property};

        return is_int($value) ? $value : null;
    }

    private function readNullableStringProperty(object $object, string $property): ?string
    {
        if (!property_exists($object, $property)) {
            return null;
        }

        $value = $object->{$property};

        return is_string($value) && '' !== $value ? $value : null;
    }
}
