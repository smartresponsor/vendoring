<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Category;

use App\Vendoring\Service\VendorAbstractCrudRouteService;

final class VendorCategoryUnassignService extends VendorAbstractCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/category';
    }

    protected function operation(): string
    {
        return 'unassign';
    }
}
