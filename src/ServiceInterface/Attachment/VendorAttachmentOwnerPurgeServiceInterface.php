<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Attachment;

interface VendorAttachmentOwnerPurgeServiceInterface
{
    public function purge(string $ownerType, string $ownerId): object;
}
