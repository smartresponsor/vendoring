<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Attachment\Bridge;

use App\Attaching\ServiceInterface\Attachment\AttachmentOwnerPurgeServiceInterface;
use App\Vendoring\ServiceInterface\Attachment\VendorAttachmentOwnerPurgeServiceInterface;

final readonly class VendorAttachingOwnerPurgeService implements VendorAttachmentOwnerPurgeServiceInterface
{
    public function __construct(private AttachmentOwnerPurgeServiceInterface $attachmentOwnerPurgeService)
    {
    }

    public function purge(string $ownerType, string $ownerId): object
    {
        return $this->attachmentOwnerPurgeService->purge($ownerType, $ownerId);
    }
}
