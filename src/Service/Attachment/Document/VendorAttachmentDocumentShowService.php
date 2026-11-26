<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Attachment\Document;

use App\Vendoring\Service\VendorAbstractCrudRouteService;

final class VendorAttachmentDocumentShowService extends VendorAbstractCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/attachment/document';
    }

    protected function operation(): string
    {
        return 'show';
    }

    protected function isReadRoute(): bool
    {
        return true;
    }
}
