<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor;

final class VendorArchiveService extends AbstractVendorCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor';
    }

    protected function operation(): string
    {
        return 'archive';
    }
}
