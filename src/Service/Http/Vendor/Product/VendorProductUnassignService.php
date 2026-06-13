<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Product;

use App\Vendoring\Service\Http\Vendor\AbstractVendorCrudRouteService;

final class VendorProductUnassignService extends AbstractVendorCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/product';
    }

    protected function operation(): string
    {
        return 'unassign';
    }
}
