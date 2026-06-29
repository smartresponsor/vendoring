<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Vendor\Attachment\Media;

use App\Vendoring\Service\Vendor\AbstractVendorCrudRouteService;

final class VendorAttachmentMediaDeleteService extends AbstractVendorCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/attachment/media';
    }

    protected function operation(): string
    {
        return 'delete';
    }
}
