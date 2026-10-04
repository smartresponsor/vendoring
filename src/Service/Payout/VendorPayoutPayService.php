<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Payout;

use App\Vendoring\Service\VendorAbstractCrudRouteService;

final class VendorPayoutPayService extends VendorAbstractCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/payout';
    }

    protected function operation(): string
    {
        return 'pay';
    }
}
