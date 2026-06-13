<?php

declare(strict_types=1);

namespace App\Vendoring\Projection\Vendor;

/**
 * Public/business identity payload for UI shells, Navigation, Interfacing, and profile cards.
 */
final readonly class VendorPublicProfileSummary
{
    public function __construct(
        public int $vendorId,
        public string $publicName,
        public string $brandName,
        public string $vendorStatus,
        public string $profileStatus,
        public ?string $publishedAt,
        public VendorPublicProfileAttachmentProjection $avatar,
        public VendorPublicProfileAttachmentProjection $cover,
        public ?string $profileUrl = null,
    ) {
    }

    /**
     * @return array{
     *   vendorId: int,
     *   publicName: string,
     *   brandName: string,
     *   vendorStatus: string,
     *   profileStatus: string,
     *   publishedAt: ?string,
     *   avatar: array<string, mixed>,
     *   cover: array<string, mixed>,
     *   profileUrl: ?string
     * }
     */
    public function toArray(): array
    {
        return [
            'vendorId' => $this->vendorId,
            'publicName' => $this->publicName,
            'brandName' => $this->brandName,
            'vendorStatus' => $this->vendorStatus,
            'profileStatus' => $this->profileStatus,
            'publishedAt' => $this->publishedAt,
            'avatar' => $this->avatar->toArray(),
            'cover' => $this->cover->toArray(),
            'profileUrl' => $this->profileUrl,
        ];
    }
}
