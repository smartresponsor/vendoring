<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Profile;

use App\Vendoring\Service\Profile\Bridge\VendorAttachingProfileAttachmentResolverService;
use App\Vendoring\ValueObject\VendorProfileAttachmentSlotValueObject;
use PHPUnit\Framework\TestCase;

final class VendorAttachingProfileAttachmentResolverServiceTest extends TestCase
{
    public function testItMapsAttachingPrimaryLinkViewToVendorAttachmentProjection(): void
    {
        $resolver = new VendorAttachingProfileAttachmentResolverService(
            new class {
                public function resolvePrimary(string $ownerType, string $ownerId, string $context, string $slot): object
                {
                    return (object) [
                        'attachment' => (object) [
                            'id' => 77,
                            'downloadUrl' => '/attachments/77/download',
                            'altText' => 'Vendor avatar',
                            'mimeType' => 'image/png',
                            'width' => 256,
                            'height' => 256,
                        ],
                    ];
                }
            },
        );

        $projection = $resolver->resolvePrimaryForVendorSlot(
            42,
            VendorProfileAttachmentSlotValueObject::SLOT_AVATAR,
        );

        self::assertSame(77, $projection->attachmentId);
        self::assertSame('/attachments/77/download', $projection->url);
        self::assertSame('Vendor avatar', $projection->altText);
        self::assertSame('image/png', $projection->mimeType);
        self::assertSame(256, $projection->width);
        self::assertSame(256, $projection->height);
    }

    public function testItIgnoresUnsupportedVendorProfileSlots(): void
    {
        $resolver = new VendorAttachingProfileAttachmentResolverService(new \stdClass());

        $projection = $resolver->resolvePrimaryForVendorSlot(42, 'invoice');

        self::assertNull($projection->attachmentId);
        self::assertNull($projection->url);
    }
}
