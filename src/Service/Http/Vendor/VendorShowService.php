<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor;

final class VendorShowService extends AbstractVendorCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor';
    }

    protected function operation(): string
    {
        return 'show';
    }

    protected function isReadRoute(): bool
    {
        return true;
    }
}
