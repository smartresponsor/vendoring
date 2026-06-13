<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Payout;

use App\Vendoring\Service\Http\Vendor\AbstractVendorCrudRouteService;

final class VendorPayoutPayService extends AbstractVendorCrudRouteService
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
