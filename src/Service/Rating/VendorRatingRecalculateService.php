<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Rating;

use App\Vendoring\Service\VendorAbstractCrudRouteService;

final class VendorRatingRecalculateService extends VendorAbstractCrudRouteService
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
