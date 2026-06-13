<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Attachment\Media;

use App\Vendoring\Service\Http\Vendor\AbstractVendorCrudRouteService;

final class VendorAttachmentMediaArchiveService extends AbstractVendorCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/attachment/media';
    }

    protected function operation(): string
    {
        return 'archive';
    }
}
