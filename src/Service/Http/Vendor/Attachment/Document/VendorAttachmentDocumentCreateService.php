<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Attachment\Document;

use App\Vendoring\Service\Http\Vendor\AbstractVendorCrudRouteService;

final class VendorAttachmentDocumentCreateService extends AbstractVendorCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/attachment/document';
    }

    protected function operation(): string
    {
        return 'create';
    }
}
