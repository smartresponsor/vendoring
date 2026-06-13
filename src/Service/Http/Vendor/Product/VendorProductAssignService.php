<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Product;

use App\Vendoring\Service\Http\Vendor\AbstractVendorCrudRouteService;

final class VendorProductAssignService extends AbstractVendorCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/product';
    }

    protected function operation(): string
    {
        return 'assign';
    }
}
