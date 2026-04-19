<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\Projection\VendorFinanceRuntimeView;
use Doctrine\DBAL\Exception;

interface VendorFinanceRuntimeViewBuilderInterface
{
    /** @throws Exception */
    public function build(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
    ): VendorFinanceRuntimeView;
}
