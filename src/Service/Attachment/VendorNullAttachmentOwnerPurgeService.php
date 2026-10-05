<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Attachment;

use App\Vendoring\ServiceInterface\Attachment\VendorAttachmentOwnerPurgeServiceInterface;

final readonly class VendorNullAttachmentOwnerPurgeService implements VendorAttachmentOwnerPurgeServiceInterface
{
    public function purge(string $ownerType, string $ownerId): object
    {
        return new \stdClass();
    }
}
