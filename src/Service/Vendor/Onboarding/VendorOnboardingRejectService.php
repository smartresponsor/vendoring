<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Vendor\Onboarding;

use App\Vendoring\Service\Vendor\AbstractVendorCrudRouteService;

final class VendorOnboardingRejectService extends AbstractVendorCrudRouteService
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
