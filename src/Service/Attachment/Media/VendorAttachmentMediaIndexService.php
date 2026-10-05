<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Attachment\Media;

use App\Vendoring\Service\VendorAbstractCrudRouteService;

final class VendorAttachmentMediaIndexService extends VendorAbstractCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/attachment/media';
    }

    protected function operation(): string
    {
        return 'index';
    }

    protected function isReadRoute(): bool
    {
        return true;
    }
}
