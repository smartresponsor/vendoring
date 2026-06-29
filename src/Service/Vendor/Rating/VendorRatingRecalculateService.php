<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Vendor\Rating;

use App\Vendoring\Service\Vendor\AbstractVendorCrudRouteService;

final class VendorRatingRecalculateService extends AbstractVendorCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/rating';
    }

    protected function operation(): string
    {
        return 'recalculate';
    }
}
