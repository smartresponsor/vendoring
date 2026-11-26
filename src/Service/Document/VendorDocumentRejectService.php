<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Document;

use App\Vendoring\Service\VendorAbstractCrudRouteService;

final class VendorDocumentRejectService extends VendorAbstractCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/document';
    }

    protected function operation(): string
    {
        return 'reject';
    }
}
