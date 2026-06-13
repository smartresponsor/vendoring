<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Commission;

use App\Vendoring\Service\Http\Vendor\AbstractVendorCrudRouteService;

final class VendorCommissionCalculateService extends AbstractVendorCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/commission';
    }

    protected function operation(): string
    {
        return 'calculate';
    }
}
