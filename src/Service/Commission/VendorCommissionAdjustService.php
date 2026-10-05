<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Commission;

use App\Vendoring\Service\VendorAbstractCrudRouteService;

final class VendorCommissionAdjustService extends VendorAbstractCrudRouteService
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
