<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Product;

use App\Vendoring\Service\VendorAbstractCrudRouteService;

final class VendorProductAssignService extends VendorAbstractCrudRouteService
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
