<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

final class VendorImportService extends VendorAbstractCrudRouteService
{
    protected function resourcePath(): string
    {
        return 'vendor';
    }

    protected function operation(): string
    {
        return 'import';
    }
}
