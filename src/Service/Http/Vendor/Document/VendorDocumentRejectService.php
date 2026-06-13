<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Document;

use App\Vendoring\Service\Http\Vendor\AbstractVendorCrudRouteService;

final class VendorDocumentRejectService extends AbstractVendorCrudRouteService
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
