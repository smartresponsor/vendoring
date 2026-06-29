<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Vendor\Document;

use App\Vendoring\Service\Vendor\AbstractVendorCrudRouteService;

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
