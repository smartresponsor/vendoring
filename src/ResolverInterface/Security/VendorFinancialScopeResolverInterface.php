<?php

declare(strict_types=1);

namespace App\Vendoring\ResolverInterface\Security;

use App\Vendoring\Entity\Vendor\VendorLedgerBindingEntity;
use Symfony\Component\HttpFoundation\Request;

interface VendorFinancialScopeResolverInterface
{
    public function resolve(Request $request, string $permission, ?string $vendorId = null): ?VendorLedgerBindingEntity;
}
