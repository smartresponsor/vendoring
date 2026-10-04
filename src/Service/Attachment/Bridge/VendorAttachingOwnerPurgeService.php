<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Attachment\Bridge;

use App\Vendoring\ServiceInterface\Attachment\VendorAttachmentOwnerPurgeServiceInterface;

final readonly class VendorAttachingOwnerPurgeService implements VendorAttachmentOwnerPurgeServiceInterface
{
    public function __construct(private object $attachmentOwnerPurgeService)
    {
    }

    public function purge(string $ownerType, string $ownerId): object
    {
        if (!method_exists($this->attachmentOwnerPurgeService, 'purge')) {
            throw new \LogicException('Configured Attaching owner purge bridge does not expose purge().');
        }

        $result = $this->attachmentOwnerPurgeService->purge($ownerType, $ownerId);
        if (!is_object($result)) {
            throw new \UnexpectedValueException('Attaching owner purge bridge must return an object result.');
        }

        return $result;
    }
}
