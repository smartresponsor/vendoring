<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Onboarding;

use App\Vendoring\Service\VendorAbstractCrudRouteService;

final class VendorOnboardingRejectService extends VendorAbstractCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor/onboarding';
    }

    protected function operation(): string
    {
        return 'reject';
    }
}
