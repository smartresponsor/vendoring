<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Vendor\Attachment\Document;

use App\Vendoring\Service\Vendor\AbstractVendorCrudRouteService;

final class VendorAttachmentDocumentNewService extends AbstractVendorCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/attachment/document';
    }

    protected function operation(): string
    {
        return 'new';
    }
}
