<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Vendor\Category;

use App\Vendoring\Service\Vendor\AbstractVendorCrudRouteService;

final class VendorCategoryAssignService extends AbstractVendorCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/category';
    }

    protected function operation(): string
    {
        return 'assign';
    }
}
