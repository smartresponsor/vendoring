<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Vendor\Commission;

use App\Vendoring\Service\Vendor\AbstractVendorCrudRouteService;

final class VendorCommissionAdjustService extends AbstractVendorCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/commission';
    }

    protected function operation(): string
    {
        return 'adjust';
    }
}
