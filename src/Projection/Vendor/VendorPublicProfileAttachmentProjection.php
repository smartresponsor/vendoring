<?php

declare(strict_types=1);

namespace App\Vendoring\Projection\Vendor;

/**
 * Vendor-side view of an attachment slot.
 *
 * The file and storage lifecycle remain owned by Attaching. Vendoring stores only the resolved
 * public-profile meaning needed by menu/profile rendering.
 */
final readonly class VendorPublicProfileAttachmentProjection
{
    public function __construct(
        public ?int $attachmentId,
        public ?string $url,
        public ?string $altText = null,
        public ?string $mimeType = null,
        public ?int $width = null,
        public ?int $height = null,
    ) {
    }

    public static function empty(): self
    {
        return new self(attachmentId: null, url: null);
    }

    /**
     * @return array{
     *   attachmentId: ?int,
     *   url: ?string,
     *   altText: ?string,
     *   mimeType: ?string,
     *   width: ?int,
     *   height: ?int
     * }
     */
    public function toArray(): array
    {
        return [
            'attachmentId' => $this->attachmentId,
            'url' => $this->url,
            'altText' => $this->altText,
            'mimeType' => $this->mimeType,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
