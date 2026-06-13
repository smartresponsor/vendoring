<?php

declare(strict_types=1);

namespace App\Vendoring\ValueObject;

/**
 * Canonical attachment owner/context/slot values used by Vendoring public profile media.
 *
 * Vendoring owns the business meaning of the profile surface. Attaching owns the stored file,
 * attachment metadata, and owner link persistence.
 */
final readonly class VendorProfileAttachmentSlotValueObject
{
    public const string OWNER_TYPE_VENDOR = 'vendor';
    public const string CONTEXT_PROFILE = 'profile';
    public const string CONTEXT_MEDIA = 'media';
    public const string SLOT_AVATAR = 'avatar';
    public const string SLOT_COVER = 'cover';
    public const string SLOT_GALLERY = 'gallery';

    private function __construct()
    {
    }

    /**
     * @return list<string>
     */
    public static function publicProfileSlots(): array
    {
        return [self::SLOT_AVATAR, self::SLOT_COVER];
    }
}
